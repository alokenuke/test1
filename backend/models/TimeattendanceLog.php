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
            [['tag_id', 'event', 'location', 'device', 'status', 'logged_by'], 'required'],
            [['tag_id', 'status', 'logged_by'], 'integer'],
            [['logged_date'], 'safe'],
            [['location'], 'string', 'max' => 32],
            [['device'], 'string', 'max' => 16]
        ];
    }
    
    public function fields() {
        return [
            'tag_id',
            'event',
            'location',
            'status',
            'logged_by',
            'device',
            'logged_date' => function() {
                return date("d M Y H:i:s", strtotime($this->logged_date));
            }
        ];
    }
    
    public function extraFields() {
        return [
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
    
    public function getUser(){
        return $this->hasOne(User::className(),['id' => 'logged_by']);
    }
}
