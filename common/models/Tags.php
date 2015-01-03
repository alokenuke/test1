<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tags".
 *
 * @property integer $id
 * @property string $uid
 * @property integer $project_id
 * @property string $tag_name
 * @property string $tag_description
 * @property integer $project_level_id
 * @property integer $user_group_id
 * @property string $product_code
 * @property integer $company_id
 * @property integer $tag_status
 * @property integer $created_by
 * @property string $created_date
 * @property string $modified_date
 *
 * @property Company $company
 * @property Projects $project
 * @property LevelFlow $projectLevel
 * @property UserGroups $userGroup
 */
class Tags extends \yii\db\ActiveRecord
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 1;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tags';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'project_id', 'tag_name', 'tag_description', 'project_level_id', 'user_group_id', 'product_code', 'company_id', 'tag_status', 'created_by', 'created_date'], 'required'],
            [['project_id', 'project_level_id', 'user_group_id', 'company_id', 'tag_status', 'created_by'], 'integer'],
            [['created_date', 'modified_date'], 'safe'],
            [['uid', 'product_code'], 'string', 'max' => 128],
            [['tag_name'], 'string', 'max' => 256],
            [['tag_description'], 'string', 'max' => 512]
        ];
    }
    
    // default scope to check company_id
    public static function find()
    {
        return parent::find()->where(['company_id' => \yii::$app->user->identity->company_id, 'tag_status' => 1]);
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => 'UID',
            'project_id' => 'Project ID',
            'tag_name' => 'Tag Name',
            'tag_description' => 'Tag Description',
            'project_level_id' => 'Project Level ID',
            'user_group_id' => 'User Group ID',
            'product_code' => 'Product Code',
            'company_id' => 'Company ID',
            'tag_status' => 'Tag Status',
            'created_by' => 'Created By',
            'created_date' => 'Created Date',
            'modified_date' => 'Modified Date',
            'Project.project_name' => 'Project Name',
            'ProjectLevel.flow_name' => 'Project Level',
            'UserGroup.group_name' => 'Group Name',
            'status.label' => 'Status',
        ];
    }
    
    public function fields()
    {
        
        $post = \Yii::$app->request->post();
        
        if(isset($post['select']))
           return $post['select'];
        
        return [
            'id',
            'type',
            'uid',
            'project_name' => function() {
                return $this->project->project_name;
            },
            'tag_name',
            'tag_description',
            'project_level' => function() {
                $projectLevel = [];
                $projectLevel[] = $this->projectLevel->level_name;
                $parent = $this->projectLevel->parent_id;
                while($parentLevelDetails = $this->getLevelDetails($parent, ['level_name', 'parent_id'])) {
                    $projectLevel[] = $parentLevelDetails->level_name;
                    $parent = $parentLevelDetails->parent_id;
                }
                return array_reverse($projectLevel);
            },
            'itemDetails',
            'userGroup',
            'product_code',
            'company_id',
            'created_date',
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(Projects::className(), ['id' => 'project_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjectLevel()
    {
        return $this->hasOne(ProjectLevel::className(), ['id' => 'project_level_id']);
    }
    
    public function getLevelDetails($id, $field)
    {
        return ProjectLevel::find()->select($field)->where(['id' => $id])->one();
    }
    
    public function getitemDetails() {
        return $this->hasOne(Items::className(), ['id' => 'tag_item_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserGroup()
    {
        return $this->hasOne(UserGroups::className(), ['id' => 'user_group_id']);
    }
}
