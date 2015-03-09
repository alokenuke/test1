<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "rel_user_levels_users".
 *
 * @property integer $id
 * @property integer $company_id
 * @property integer $user_group_id
 * @property integer $user_level_id
 * @property integer $user_id
 * @property integer $assigned_by
 * @property string $created_date
 */
class RelUserLevelsUsers extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rel_user_levels_users';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'user_group_id', 'user_level_id', 'user_id', 'assigned_by'], 'required'],
            [['company_id', 'user_group_id', 'user_level_id', 'user_id', 'assigned_by'], 'integer'],
            [['user_id'], 'unique', 'targetAttribute' => ['company_id', 'user_group_id', 'user_level_id', 'user_id']],
            [['created_date'], 'safe']
        ];
    }
    
    public static function find()
    {
        $query = parent::find()->where(['rel_user_levels_users.company_id' => \yii::$app->user->identity->company_id]);
        
        return $query;
    }
    
    public function fields()
    {
        
        $post = \Yii::$app->request->post();
        
        if(isset($post['select']))
           return $post['select'];
        
        return [
            'id',
            'user_group_id',
            'user_level_id',
            'user_id',
            'users',
            'created_date'
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Company ID',
            'user_group_id' => 'User Group ID',
            'user_level_id' => 'User Level ID',
            'user_id' => 'User ID',
            'assigned_by' => 'Assigned By',
            'created_date' => 'Created Date',
        ];
    }
    
    public function getUsers()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    
    public function getUserGroup()
    {
        return $this->hasOne(UserGroups::className(), ['user_group_id' => 'id']);
    }
    
    public function actDelete() {
        return $this->delete();
    }
}
