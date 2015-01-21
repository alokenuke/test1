<?php

namespace backend\models;

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
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 1;
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
            [['group_name'], 'required'],
            [['company_id', 'group_status', 'created_by'], 'integer'],
            ['group_status', 'default', 'value' => self::STATUS_ACTIVE],
            ['company_id', 'default', 'value' => \yii::$app->user->identity->company_id],
            ['created_by', 'default', 'value' => \yii::$app->user->id],
            ['created_date', 'default', 'value' => date("Y-m-d H:i:s")],
            [['created_date', 'updated_date'], 'safe'],
            [['group_name', 'group_notes'], 'string', 'max' => 256]
        ];
    }
        
    public static function find()
    {   
        $post = \Yii::$app->request->post();
        
        $select = ['user_groups.id', 'user_groups.group_name'];

        if(isset($post['select']['UserGroups']))
           $select = $post['select']['UserGroups'];

        $query = parent::find()->select($select)->where(['user_groups.company_id' => \yii::$app->user->identity->company_id, 'group_status' => 1])->joinWith("projectIds");
        
        return $query;
    }
    
    public function fields()
    {
        return [
            'id',
            'group_name',
            'group_status',
        ];
    }
    
    public function extraFields()
    {
        return [
            'levels',
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
    
    public function getLevels()
    {
        return $this->hasMany(UserLevels::className(), ['user_group_id' => 'id']);
    }
    
    public function actDelete() {
        $this->group_status = 2;
        return $this->save();
    }
}
