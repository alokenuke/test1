<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "tag_activity_log".
 *
 * @property integer $id
 * @property integer $tag_id
 * @property integer $process_stage_id
 * @property string $process_stage_answer
 * @property string $comment
 * @property string $location
 * @property string $device
 * @property integer $status
 * @property integer $logged_by
 * @property string $logged_date
 */
class TimeattendanceLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'timeattendance_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tag_id', 'login_time', 'location', 'device', 'status', 'logged_by'], 'required'],
            [['tag_id', 'status', 'logged_by'], 'integer'],
            [['logout_time'], 'safe'],
            [['location'], 'string', 'max' => 256],
            [['device'], 'string', 'max' => 16]
        ];
    }
    
    public function fields() {
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
            'tag_id',
            'login_time' => function() {
                return date("d M Y H:i:s", strtotime($this->login_time));
            },
            'logout_time' => function() {
                return date("d M Y H:i:s", strtotime($this->logout_time));
            },
            'hours_logged' => function() {
                if($this->logout_time) {
                    $datetime1 = date_create($this->login_time);
                    $datetime2 = date_create($this->logout_time);
                    $interval = date_diff($datetime1, $datetime2);
                    return $interval->format('%h.%i hours');
                }
            },
            'location',
            'status',
            'logged_by',
            'device',
        ];
    }
    
    public function extraFields() {
        return [
                'timeattendance',
                'user',
            ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tag_id' => 'Tag ID',
            'location' => 'Location',
            'device' => 'Device',
            'status' => 'Status',
            'logged_by' => 'Logged By',
            'logged_date' => 'Logged Date',
        ];
    }
    
    public function getProjectLevel()
    {
        return $this->hasOne(ProjectLevel::className(), ['id' => 'project_level_id'])->via('timeattendance');
    }
    
    public function getLevelDetails($id, $field)
    {
        return ProjectLevel::find()->select($field)->where(['project_level.id' => $id])->one();
    }
    
    public function getTimeattendance(){
        return $this->hasOne(Timeattendance::className(),['id' => 'tag_id']);
    }
    
    public function getUser(){
        return $this->hasOne(User::className(),['id' => 'logged_by']);
    }
}