<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "user_tokens".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $token
 * @property string $expire_on
 */
class UserTokens extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_tokens';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['token'], 'string', 'max' => 100],
            [['login_ip', 'login_location', 'token', 'request_from'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'token' => 'Token',
            'expire_on' => 'Expiry',
        ];
    }
    
    public function fields() {
        return [
            'user_id',
            'token',
            'login_ip',
            'login_location',
            'login_latlong',
            'created_on',
            'request_from',
            'expire_on',
            'expiry_status'
        ];
    }
    
    public function extraFields() {
        return [
            'user'
        ];
    }
    
    /**
     * Generates new password reset token
     */
    public function generateToken()
    {
        $this->token = Yii::$app->security->generateRandomString();
    }
    
    public function getUser(){
        return $this->hasOne(User::className(),['id' => 'user_id']);
    }
}
