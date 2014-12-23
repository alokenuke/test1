<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property integer $role
 * @property string $auth_key
 * @property string $username
 * @property string $password
 * @property string $password_reset_token
 * @property integer $designation
 * @property string $contact_number
 * @property string $photo
 * @property integer $status
 * @property integer $company_id
 * @property string $last_login
 * @property string $created_date
 * @property string $modified_date
 */
class User extends common\models\User implements IdentityInterface
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['first_name', 'email', 'role', 'auth_key', 'username', 'password', 'password_reset_token', 'last_login', 'created_date'], 'required'],
            [['role', 'designation', 'status', 'company_id'], 'integer'],
            [['last_login', 'created_date', 'modified_date'], 'safe'],
            [['first_name', 'last_name', 'email'], 'string', 'max' => 256],
            [['auth_key'], 'string', 'max' => 32],
            [['username', 'password', 'contact_number', 'photo'], 'string', 'max' => 128],
            [['password_reset_token'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email',
            'role' => 'Role',
            'auth_key' => 'Auth Key',
            'username' => 'Username',
            'password' => 'Password',
            'password_reset_token' => 'Password Reset Token',
            'designation' => 'Designation',
            'contact_number' => 'Contact Number',
            'photo' => 'Photo',
            'status' => 'Status',
            'company_id' => 'Company ID',
            'last_login' => 'Last Login',
            'created_date' => 'Created Date',
            'modified_date' => 'Modified Date',
        ];
    }
}
