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
class Timeattendance extends \yii\db\ActiveRecord
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 1;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'timeattendance';
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
        
        $fileManager = new FileManager();
        
        $qrCodePath = $fileManager->getPath("Attendanceqrcode")."/";
        $barCodePath = $fileManager->getPath("Attendancebarcode")."/";
        
        if(!file_exists($qrCodePath.$this->uid.".png")) {
            //error_reporting(0);
            $qrCode= new BarCodeGenerator\DNS2DBarcode();
            $qrCode->save_path= $qrCodePath;
            $qrCode->getBarcodePNGPath($this->uid, 'qrcode',10, 10);
        }
        if(!file_exists($barCodePath.$this->uid.".png")) {
            //error_reporting(0);
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
        
        $return = "TA-".substr(str_replace(".", "", $randomData), 0, $length);
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
            'company_id' => 'Company ID',
            'tag_status' => 'Tag Status',
            'created_by' => 'Created By',
            'created_date' => 'Created Date',
            'modified_date' => 'Modified Date',
            'Project.project_name' => 'Project Name',
            'UserGroup.group_name' => 'Group Name',
        ];
    }
    
    public function fields()
    {
        return [
            'id',
            'uid',
            'project_id',
            'project_name' => function() {
                if($this->project)
                    return $this->project->project_name;
            },
            'tag_name',
            'user_group_id',
            'project_level_id',
            'tag_description',
            'created_date',
        ];
    }
    
    public function extraFields() {
        return [
            'project_level' => function() {
                $projectLevel = [];
                $projectLevel[] = $this->projectLevel->level_name;
                $parent = $this->projectLevel->parent_id;
                while($parentLevelDetails = $this->getLevelDetails($parent, [])) {
                    $projectLevel[] = $parentLevelDetails->level_name;
                    $parent = $parentLevelDetails->parent_id;
                }
                return array_reverse($projectLevel);
            },
            'timeAttendanceAssignmentObj' => function() {
                $timeAttendanceAssignment = [];
                
                foreach($this->timeAttendanceAssignment as $v) {
                    $temp = [];
                    
                    $timeAttendanceAssignment["$v[user_id]"] = $temp;
                    
                    $timeAttendanceAssignment["$v[user_id]"]['userDetails'] = User::findOne(['id' => $v['user_id']]);
                    $userLevel = RelUserLevelsUsers::findOne(['user_group_id' => $this->user_group_id, 'user_id' => $v['user_id']]);
                    if($userLevel) {
                        $timeAttendanceAssignment["$v[user_id]"]['user_level'] = UserLevels::findOne(['id' => $userLevel->user_level_id]);
                    }
                }

                return $timeAttendanceAssignment;
            },
            'userGroup',
            'timeAttendanceAssignment',
            'timeattendanceLog'        ];
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
        return ProjectLevel::find()->select($field)->where(['project_level.id' => $id])->one();
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
    public function getTimeAttendanceAssignment()
    {
        return $this->hasMany(TimeattendanceAssignment::className(), ['tag_id' => 'id']);
    }

    public function getTimeattendanceLog()
    {
        return $this->hasOne(TimeattendanceLog::className(), ['tag_id'=>'id'])->orderBy("logged_date");
    }
}
