<?php
namespace backend\models;
use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;

/**
 * Password change form
 */
class ChangePassword extends Model
{
    public $old_password, $new_password, $repeat_password;

    /**
     * @var \common\models\User
     */
    private $_user;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['old_password', 'new_password', 'repeat_password'], 'required'],
            [['old_password', 'new_password', 'repeat_password'], 'string', 'min' => 6],
            [['repeat_password'], 'compare', 'compareAttribute' => 'new_password', 'message' => 'Passwords do not match'],
            ['old_password', 'validatePassword'],
        ];
    }

    /**
     * Resets password.
     *
     * @return boolean if password was reset.
     */
    public function resetPassword()
    {
        $user = $this->_user;
        $user->password = $this->password;
        $user->removePasswordResetToken();

        return $user->save();
    }
    
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = \yii::$app->user->identity;
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect old password.');
            }
        }
    }
}
