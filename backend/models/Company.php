<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "company".
 *
 * @property integer $id
 * @property string $company_name
 * @property integer $company_owner
 * @property string $company_logo
 * @property integer $company_status
 * @property string $expiry_date
 * @property string $created_date
 */
class Company extends \yii\db\ActiveRecord
{
    public $sampleTagParams = [];
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    /**
     * @inheritdoc
     */
    
    public $temp_path;


    public static function tableName()
    {
        return 'company';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_name', 'company_owner', 'company_logo', 'company_status', 'expiry_date', 'membership_id'], 'required'],
            [['company_status'], 'integer'],
            ['company_name', 'unique'],
            ['company_status', 'default', 'value' => self::STATUS_ACTIVE],
            [['expiry_date', 'created_date'], 'safe'],
            [['company_name'], 'string', 'max' => 256],
            [['company_logo'], 'string', 'max' => 128]
        ];
    }
    
    public function afterSave($insert, $changedAttributes) {
        
        if($this->temp_path) {
            $fileManager = new FileManager($this->id);
            $projectPath = $fileManager->getPath("")."/";

            if(file_exists("temp/".$this->company_logo))
                rename("temp/".$this->company_logo, $projectPath.$this->company_logo);
            
            if(isset($changedAttributes['company_logo']) && $changedAttributes['company_logo'] && file_exists($projectPath.$changedAttributes['company_logo']))
                unlink($projectPath.$changedAttributes['company_logo']);
        }
        
        if(isset($changedAttributes['membership_id'])){
            $model = new CompanyMembershipLogs();
            $data = ['company_id'=> $this->id,'membership_id'=>$this->membership_id];
            $model->setAttributes($data);
            $model->save(false);
        }
        
        if($insert) {
            // Create required folders
            $fileManager = new FileManager($this->id);
            $fileManager->createFolders();
            
            $labelTemplates = LabelTemplates::findAll(['company_id' => 0]);
            $countLabels = count($labelTemplates);
            for($i = 0;$i<$countLabels;$i++) {
                $temp = $labelTemplates[$i];
                $temp->id = NULL;
                $temp->company_id = $this->id;
                $temp->isNewRecord = true;
                $temp->insert(0);
            }
            
            $reportTemplates = ReportTemplates::findAll(['company_id' => 0]);
            
            foreach($reportTemplates as $reportTemplate) {
                $reportTemplate->id = NULL;
                $reportTemplate->isNewRecord = true;
                $reportTemplate->company_id = $this->id;
                $reportTemplate->insert(0);
            }
            
            $projectModel = Projects::findOne(['company_id' => 0]);
            $projectModel->id = NULL;
            $projectModel->company_id = $this->id;
            $projectModel->isNewRecord = true;
            $projectModel->save(0);
            $this->sampleTagParams['project_id'] = $projectModel->id;
            
            $userGroupsModel = new UserGroups();
            $userGroupsModel->company_id = $this->id;
            $userGroupsModel->group_name = "Group A";
            $userGroupsModel->group_status = 1;
            $userGroupsModel->save(0);
            $this->sampleTagParams['user_group_id'] = $userGroupsModel->id;
            
            $userLevelsMOdel = new UserLevels();
            $userLevelsMOdel->company_id = $this->id;
            $userLevelsMOdel->user_group_id = $this->sampleTagParams['user_group_id'];
            $userLevelsMOdel->level_name = "Super Users";
            $userLevelsMOdel->status = 1;
            $userLevelsMOdel->created_by = \yii::$app->user->id;
            $userLevelsMOdel->save(0);
            $this->sampleTagParams['user_level_id'] = $userLevelsMOdel->id;
            
            $userGroupProjectModel = new UserGroupProjects();
            $userGroupProjectModel->user_group_id = $this->sampleTagParams['user_group_id'];
            $userGroupProjectModel->project_id = $this->sampleTagParams['project_id'];
            $userGroupProjectModel->assigned_by = \yii::$app->user->id;
            $userGroupProjectModel->save(0);
            
        }
        
        parent::afterSave($insert, $changedAttributes);
    }

    public function fields() {
        return [
            'id',
            'company_name',
            'company_owner',
            'company_logo' => function() {
                if($this->company_logo)
                    return "filemanager/getimage?company=".$this->id."&type=&file=".$this->company_logo;
            },
            'membership_id',
            'membership',
            'company_status',
            'expiry_status' => function() {
                if(strtotime($this->expiry_date) < time())
                    return true;
            },
            'expiry_date' => function() {
                if(strtotime($this->expiry_date))
                    return date("d M Y", strtotime($this->expiry_date));
            }
        ];
    }
    
    public function extraFields() {
        return [
            'membership',
            'user' => function() {
                $roleObj = Roles::find()->where(['company_id' => $this->id, 'role_name' => 'Super Admin'])->one();
                
                if($roleObj) {
                    $role = $roleObj->id;
                    return User::find()->where(['company_id' => $this->id])->andWhere(['role' => $role])->one();
                }
            },
            'stats' => function() {
                $return = array();
                $return['projects']['count'] = Projects::find()->where(['company_id' => $this->id])->count();
                $return['tags']['count'] = Tags::find()->where(['company_id' => $this->id])->count();
                $return['users']['count'] = User::find()->where(['company_id' => $this->id])->andWhere(['user.status' => 1])->count();
                $return['items']['count'] = Items::find()->where(['company_id' => $this->id])->count();
                
                $fileManager = new \backend\models\FileManager($this->id);

                $rootPath = $fileManager->getRootPath();

                if(!file_exists($rootPath))
                    mkdir ($rootPath);
                
                $return['data']['count'] = $this->getFolderSize($rootPath);
                
                return $return;
            }
        ];
    }
    
    public static function find() {
        return parent::find()->andWhere(['<>', 'company_status', 2]);
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_name' => 'Company Name',
            'company_owner' => 'Company Owner',
            'company_logo' => 'Company Logo',
            'company_status' => 'Company Status',
            'expiry_date' => 'Expiry Date',
            'created_date' => 'Created Date',
        ];
    }
    
    public function actDelete() {
        $this->company_status = 2;
        return $this->save();
    }
    
    public function getMembership()
    {
        return $this->hasOne(Membership::className(), ['id' => 'membership_id']);
    }
    
    public function getFolderSize($path) {
        
        $path = realpath($path);
        
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $obj = new \COM ( 'scripting.filesystemobject' );
                        
            if ( is_object ( $obj ) )
            {
                $ref = $obj->getfolder ( $path );
                $size = $ref->size;
                $obj = null;
            }
            else
            {
                echo 'can not create object';
            }
        } else {
            $io = popen ( '/usr/bin/du -sk ' . $path, 'r' );
            $size = fgets ( $io, 4096);
            $size = substr ( $size, 0, strpos ( $size, "\t" ) );
            pclose ( $io );
        }
        return $this->format_size($size);
    }
    
    public function format_size($size, $currentSize="") {
        $units = explode(' ', 'B KB MB GB TB PB');
        
        $mod = 1024;

        for ($i = 0; $size > $mod; $i++) {
            $size /= $mod;
        }

        $endIndex = strpos($size, ".")+3;
        
        if($currentSize) {
            $i += array_search($currentSize, $units);
        }

        return substr( $size, 0, $endIndex).' '.$units[$i];
    }
    
    public function createSampleTags($company_id) {
        
        // Create tag Group
        $itemGroup = $this->createRecord(new Items(), ['company_id' => $company_id, 'item_name' => 'Construction Items', 'parent_id' => 0, 'status' => 1]);
        //Create Tag Item Type
        $itemType = $this->createRecord(new Items(), ['company_id' => $company_id, 'item_name' => 'ACs', 'parent_id' => $itemGroup, 'status' => 1]);
        //Assign item to project
        $this->createRecord(new ItemsProjects(), ['item_id' => $itemGroup, 'project_id' => $this->sampleTagParams['project_id']]);
        
        $processDetails = [];
        $temp = [];
        // Create Process group
        $tagProcess = TagProcess::find()->andWhere(['company_id' => 0])->orderBy("parent_id ASC, position ASC")->all();
        foreach($tagProcess as $process) {
            $oldId = $process->id;
            if(!isset($processDetails[$process->type]))
                $processDetails[$process->type] = [];
            $process->id = NULL;
            $process->company_id = $company_id;
            if($process->parent_id > 0)
                $process->parent_id = $temp[$process->parent_id];
            $process->isNewRecord = true;
            $process->status = 1;
            $process->save(0);
            $temp[$oldId] = $process->id;
            $processDetails[$process->type][] = $process->id;
        }
        
        // Assign process to project
        $this->createRecord(new TagProcessProjects(), ['process_id' => $processDetails[0][0], 'project_id' => $this->sampleTagParams['project_id'], 'status' => 1]);
        
        // Relate user group with user
        $this->createRecord(new RelUserLevelsUsers(), ['company_id' => $company_id, 'user_group_id' => $this->sampleTagParams['user_group_id'], 'user_level_id' => $this->sampleTagParams['user_level_id'], 'user_id' => $this->sampleTagParams['user_id']]);
        
        // relate process with project
        $this->createRecord(new RelItemProcess(), ['company_id' => $company_id, 'item_type_id' => $itemType, 'process_flow_id' => $processDetails[1][0]]);
                
        // create Project levels
        $projectLevel = $this->createRecord(new ProjectLevel(), ['company_id' => $company_id, 'level_name' => "Level 1", 'parent_id' => 0, 'position' => 0, 'status' => 1]);
        
        // Assign project level to project
        $this->createRecord(new ProjectLevelProjects(), ['level_id' => $projectLevel, 'project_id' => $this->sampleTagParams['project_id']]);
        
        // create tag
        $tagModel = new Tags();
        $tagId = $this->createRecord($tagModel, ['type' => 'sT', 'uid' => $tagModel->generateUID(10), 'project_id' => $this->sampleTagParams['project_id'], 'tag_name' => "AC Installation Work", 'tag_description' => "AC Installation work description", 'project_level_id' => $projectLevel, 'user_group_id' => $this->sampleTagParams['user_group_id'], 'tag_item_id' => $itemType, 'tag_process_flow_id' => $processDetails[1][0], 'product_code' => "ACB22JJA1", 'company_id' => $company_id, 'tag_status' => 1]);
        
        // Tag Assignment
        $this->createRecord(new TagAssignment(), ['tag_id' => $tagId, 'user_id' => $this->sampleTagParams['user_id'], 'process_stage_from' => $processDetails[2][0], 'process_stage_to' => $processDetails[2][count($processDetails[2])-1], 'mandatory' => 0, 'notification_status' => 'assigned', 'notification_frequency' => 'weekly'], 0, 1);
        
    }
    
    public function createRecord($model, $params, $validate=0, $debug=0) {
        $model->setAttributes($params);
        if($debug) {
            print_r($model);
        }
        $model->save($validate);
        if(isset($model->id))
            return $model->id;
    }
}