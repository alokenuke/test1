<?php

namespace common\models;

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
            [['company_id', 'user_group_id', 'level_name', 'status', 'created_by'], 'required'],
            [['company_id', 'user_group_id', 'status', 'created_by'], 'integer'],
            [['created_date'], 'safe'],
            [['level_name'], 'string', 'max' => 128]
        ];
    }
    
    public static function find()
    {
        $query = parent::find()->where(['company_id' => \yii::$app->user->identity->company_id, 'status' => 1]);
        
        $post = \Yii::$app->request->post();
        
        $select = "*";
        
        if(isset($post['select']))
           $select = $post['select'];
        
        $query->select($select);
        
        if(isset($post['search'])) {
            foreach($post['search'] as $key => $val)
                if(isset($val))
                    $query->andWhere([$key => $val]);
        }
        
        return $query;
    }
    
    public function fields()
    {
        return [
            'id',
            'company_id',
            'user_group_id',
            'level_name',
            'relateUsers',
            'status',
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
        return $this->hasOne(RelUserLevelsUsers::className(), ['user_level_id' => 'id']);
    }
}
