<?php

namespace backend\models;

use \Yii;

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
            [['uid', 'project_id', 'tag_name', 'tag_description', 'project_level_id', 'user_group_id'], 'required'],
            [['project_id', 'project_level_id', 'user_group_id', 'company_id', 'tag_status', 'created_by'], 'integer'],
            [['created_date', 'modified_date'], 'safe'],
            
            ['uid', 'unique', 'targetAttribute' => ['company_id', 'uid']],
            ['tag_name', 'unique', 'targetAttribute' => ['company_id', 'tag_name']],
            
            [['product_code'], 'string', 'max' => 128],
            [['tag_name'], 'string', 'max' => 256],
            [['tag_description'], 'string', 'max' => 512],
            ['tag_status', 'default', 'value' => self::STATUS_ACTIVE],
            ['tag_status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
            ['company_id', 'default', 'value' => \yii::$app->user->identity->company_id],
            ['created_by', 'default', 'value' => \yii::$app->user->identity->id],
            ['created_date', 'default', 'value' => date("Y-m-d H:i:s")],
        ];
    }
    
    public function afterSave($insert, $changedAttributes) {
        
        $qrCodePath = "userUploads/".\yii::$app->user->identity->company_id . "/tagsImages/qrCode/";
        $barCodePath = "userUploads/".\yii::$app->user->identity->company_id . "/tagsImages/barCode/";
        
        if(!file_exists($qrCodePath.$this->uid.".png")) {
            error_reporting(0);
            $qrCode= new BarCodeGenerator\DNS2DBarcode();
            $qrCode->save_path= $qrCodePath;
            $qrCode->getBarcodePNGPath($this->uid, 'qrcode',10, 10);
        }
        if(!file_exists($barCodePath.$this->uid.".png")) {
            error_reporting(0);
            $barCode= new BarCodeGenerator\DNS1DBarcode();
            $barCode->save_path= $barCodePath;
            $barCode->getBarcodePNGPath($this->uid, 'C39', 5, 200);
        }
        
        parent::afterSave($insert, $changedAttributes);
    }
    
    static public function generateUID($length)
    {
        if (is_readable('/dev/urandom')) {
            $randomData = base64_encode(file_get_contents('/dev/urandom', false, null, 0, $length) . uniqid(mt_rand(), true));
        } else {
            $randomData = uniqid(Yii::$app->security->generateRandomString(6), true);
        }
        
        $return = substr(str_replace(".", "", $randomData), 0, $length);
        return $return;
    }
    
    // default scope to check company_id
    public static function find()
    {
        $query = parent::find()->where(['company_id' => \yii::$app->user->identity->company_id, 'tag_status' => 1]);
        
        return $query;
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
        return [
            'id',
            'type',
            'uid',
            'project_id',
            'project_name' => function() {
                return $this->project->project_name;
            },
            'tag_name',
            'tag_item_id',
            'tag_process_flow_id',
            'user_group_id',
            'project_level_id',
            'tag_description',
            'product_code',
            'created_date',
        ];
    }
    
    public function extraFields() {
        return [
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
            'projectLevelObj' => function() {
                $projectLevel = [];
                $projectLevel[] = $this->projectLevel;
                $parent = $this->projectLevel->parent_id;
                while($parentLevelDetails = $this->getLevelDetails($parent, ['id', 'level_name', 'parent_id'])) {
                    $projectLevel[] = $parentLevelDetails;
                    $parent = $parentLevelDetails->parent_id;
                }
                return array_reverse($projectLevel);
            },
            'itemObj' => function() {
                $items = [];
                $items[] = $this->itemDetails;
                $parent = $this->itemDetails->parent_id;
                
                while($parentDetails = $this->getItemParentDetails($parent, ['id', 'item_name', 'parent_id'])) {
                    $items[] = $parentDetails;
                    $parent = $parentDetails->parent_id;
                }
                return array_reverse($items);
            },
            'processObj' => function() {
                $process = [];
                $process[] = $this->processDetails;
                $parent = $this->processDetails->parent_id;
                
                while($parentDetails = $this->getProcessParentDetails($parent, ['id', 'process_name', 'parent_id'])) {
                    $process[] = $parentDetails;
                    $parent = $parentDetails->parent_id;
                }
                return array_reverse($process);
            },
            'tagAssignmentObj' => function() {
                $tagAssignment = [];
                
                foreach($this->tagAssignment as $v) {
                    
                    $v['process_stage_from'] = TagProcess::find()->select(["id", 'process_name', 'status'])->andWhere(['id' => $v['process_stage_from']])->one();
                    $v['process_stage_to'] = TagProcess::find()->select(["id", 'process_name', 'status'])->andWhere(['id' => $v['process_stage_to']])->one();
                    
                    $noti_status = [];
                    if($v['notification_status']=='all')
                        $noti_status[] = ['id' => 'all', 'name' => 'All process'];
                    if($v['notification_status']=='assigned')
                        $noti_status[] = ['id' => 'assigned', 'name' => 'Assigned process'];
                    else if($notification_statuses = TagUserNotificationStatus::findAll(['tag_id' => $this->id, 'tag_assignment_id' => $v['id']])) {
                        $statusid = [];
                        
                        foreach($notification_statuses as $status) {
                            $statusid[] = $status['process_stage_id'];
                        }
                        $process_stages = TagProcess::find()->select(['id', 'process_name'])->andWhere(['id' => $statusid])->all();
                        foreach($process_stages as $stage)
                            $noti_status[] = ['id' => $stage['id'], 'name' => $stage['process_name']];
                    }
                    
                    $v['notification_status'] = $noti_status;
                    
                    $tagAssignment["$v[user_id]"] = $v;
                }

                return $tagAssignment;
            },
            'itemDetails',
            'userGroup',
            'tagAssignment'
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
    
    public function getItemDetails() {
        return $this->hasOne(Items::className(), ['id' => 'tag_item_id']);
    }
    
    public function getItemParentDetails($parentId, $fields)
    {
        return Items::find()->select($fields)->where(['id' => $parentId])->one();
    }
    
    public function getProcessDetails() {
        return $this->hasOne(TagProcess::className(), ['id' => 'tag_process_flow_id']);
    }
    
    public function getProcessParentDetails($parentId, $fields)
    {
        return TagProcess::find()->select($fields)->where(['id' => $parentId])->one();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserGroup()
    {
        return $this->hasOne(UserGroups::className(), ['id' => 'user_group_id']);
    }
    
    public function actDelete() {
        $this->tag_status = 2;
        return $this->save();
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTagAssignment()
    {
        return $this->hasMany(TagAssignment::className(), ['tag_id' => 'id']);
    }
}
