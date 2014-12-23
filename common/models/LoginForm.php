<?php
namespace common\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            $userDetails = Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
            
            $authKey = \Yii::$app->session->get('user.auth_key');
            
            $authToken = new UserTokens();
            
            $authToken->user_id = \yii::$app->user->id;
            $authToken->token = $authKey;
            $authToken->created_on = time();
            
            $authToken->login_ip = Yii::$app->getRequest()->getUserIP();
            
            // Check if similar ip detected by system in last 6 months
            $locationDetails = UserTokens::find()
                        ->andWhere(['login_ip' => $authToken->login_ip])
                        ->andWhere("created_on > ".strtotime("-6 months"))
                        ->andWhere("login_location != 'Not Available'")
                        ->orderBy("created_on DESC")
                        ->one();
            
            if(!$locationDetails) {
                $locationDetails = unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip='.$authToken->login_ip));
                
                if(empty($locationDetails['geoplugin_region'])) {
                    $location = "Not Available";
                }
                else
                    $location = $locationDetails['geoplugin_region']." (".$locationDetails['countryName'].")";
            }
            else {
                $location = $locationDetails->login_location;
            }
            
            $authToken->login_location = $location;
            
            $authToken->request_from = "webapp";
            $authToken->expire_on = $authToken->created_on+\yii::$app->params['tokenExpiryTime'];
            
            $authToken->save();
            
            return $userDetails;
        } else {
            return false;
        }
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }
}
