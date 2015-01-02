<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_groups".
 *
 * @property integer $id
 * @property integer $company_id
 * @property string $group_name
 * @property string $group_notes
 * @property integer $group_status
 * @property integer $created_by
 * @property string $created_date
 * @property string $updated_date
 *
 * @property Tags[] $tags
 */
class UserGroups extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_groups';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'group_name', 'group_notes', 'group_status', 'created_by', 'created_date'], 'required'],
            [['company_id', 'group_status', 'created_by'], 'integer'],
            [['created_date', 'updated_date'], 'safe'],
            [['group_name', 'group_notes'], 'string', 'max' => 256]
        ];
    }
        
    public static function find()
    {
        $query = parent::find()->where(['company_id' => \yii::$app->user->identity->company_id, 'group_status' => 1])
            ->joinWith("projectIds");
        
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
            'group_name',
            'group_status',
            'created_by',
            'projectIds'
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
            'group_name' => 'Group Name',
            'group_notes' => 'Group Notes',
            'group_status' => 'Group Status',
            'created_by' => 'Created By',
            'created_date' => 'Created Date',
            'updated_date' => 'Updated Date',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTags()
    {
        return $this->hasMany(Tags::className(), ['user_group_id' => 'id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjectIds()
    {
        return $this->hasMany(UserGroupProjects::className(), ['user_group_id' => 'id']);
    }
}
