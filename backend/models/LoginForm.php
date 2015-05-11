<?php
namespace backend\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $device;
    public $location;
    public $rememberMe = true;

    private $_user = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password', 'device'], 'required'],
            [['device', 'location'], 'safe'],
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
                return;
            }
            
            if ($this->device == 'webapp' && !$user->allow_be) {
                $this->addError("password", 'You are not allowed to login through backend. Please contact your administrator.');
            }
            else if($this->device == 'mobile' && $user->company_id == 0) {
                $this->addError("password", 'You are not allowed to login through mobile.');
            }
            else {
                if($user->company_id > 0) {
                    $companyModel = Company::findOne(['id' => $user->company_id]);
                    if(strtotime($companyModel->expiry_date) < time())
                        $this->addError("password", 'Your subscription has expired. Please contact administrator.');
                    else if(!$companyModel)
                        $this->addError("password", 'Your details are removed from the system. Please contact administrator.');
                }
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
            
            $roleObj = Roles::find()->andWhere(['id' => \yii::$app->user->identity->role])->one();
            \yii::$app->session->set('user.role_details', $roleObj);
            
            $authKey = \Yii::$app->session->get('user.auth_key');
            
            $authToken = new UserTokens();
            
            $authToken->user_id = \yii::$app->user->id;
            $authToken->token = $authKey;
            $authToken->created_on = time();
            
            $authToken->login_ip = Yii::$app->getRequest()->getUserIP();
            
            if($authToken->login_ip != "::1") {
                
                $lastLoginQry = UserTokens::find()
                        ->andWhere("created_on > ".strtotime("-15 days"))
                        ->andWhere("login_location != 'Not Available'")
                        ->orderBy("created_on DESC");
                
                if($this->location)
                    $lastLoginQry = $lastLoginQry->andWhere(['login_latlong' => $authToken->login_location['lat'].",".$authToken->login_location['long']]);
                else
                    $lastLoginQry = $lastLoginQry->andWhere(['login_ip' => $authToken->login_ip]);
                
                // Check if similar ip detected by system in last 6 months
                $locationDetails = $lastLoginQry->one();
                
                if(!$locationDetails) {
                    if($this->location)
                        $locationDetails = unserialize(file_get_contents("http://www.geoplugin.net/extras/location.gp?format=php&lat=$this->location[lat]&long=$this->location[long]"));
                    else
                        $locationDetails = unserialize(file_get_contents("http://www.geoplugin.net/php.gp?ip=".$authToken->login_ip));
                    
                    if(empty($locationDetails['geoplugin_region'])) {
                        $authToken->login_location = "Not available";
                        $authToken->login_latlong = "Not available";
                    }
                    else {
                        $authToken->login_location = $locationDetails['geoplugin_region']." (".$locationDetails['geoplugin_countryName'].")";
                        
                        if($this->location)
                            $authToken->login_latlong = $this->location['lat'].",".$this->location['long'];
                        else
                            $authToken->login_latlong = $locationDetails['geoplugin_latitude'].",".$locationDetails['geoplugin_longitude'];
                    }
                }
                else {
                    $authToken->login_location = $locationDetails->login_location;
                    $authToken->login_latlong = $locationDetails->login_latlong;
                }
            }
            else {
                $authToken->login_location = "Not available";
                $authToken->login_latlong = "Not available";
            }
            $authToken->request_from = $this->device?$this->device:"webapp";
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
