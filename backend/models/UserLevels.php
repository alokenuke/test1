<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "user_levels".
 *
 * @property integer $id
 * @property integer $company_id
 * @property integer $user_group_id
 * @property string $level_name
 * @property integer $status
 * @property integer $created_by
 * @property string $created_date
 */
class UserLevels extends \yii\db\ActiveRecord
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 1;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_levels';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_group_id', 'level_name'], 'required'],
            [['company_id', 'user_group_id', 'status', 'created_by'], 'integer'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['company_id', 'default', 'value' => \yii::$app->user->identity->company_id],
            ['created_by', 'default', 'value' => \yii::$app->user->id],
            [['created_date'], 'safe'],
            [['level_name'], 'string', 'max' => 128]
        ];
    }
    
    public static function find()
    {
        $query = parent::find()->where(['user_levels.company_id' => \yii::$app->user->identity->company_id, 'status' => 1]);
        
        return $query;
    }
    
    public function fields()
    {
        $post = \Yii::$app->request->post();
        
        if(isset($post['select']))
           return $post['select'];
        
        return [
            'id',
            'company_id',
            'user_group_id',
            'level_name',
            'status',
            'created_date'
        ];
    }
    
    public function extraFields()
    {
        return [
            'relateUsers',
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
            'level_name' => 'Level Name',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_date' => 'Created Date',
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRelateUsers()
    {
        return $this->hasMany(RelUserLevelsUsers::className(), ['user_level_id' => 'id']);
    }
    
    public function actDelete() {
        $this->status = 2;
        return $this->save();
    }
}