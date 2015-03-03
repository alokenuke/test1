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
class TagActivityLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tag_activity_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tag_id', 'process_stage_id', 'process_stage_answer', 'comment'], 'required'],
            [['tag_id', 'process_stage_id', 'status', 'logged_by'], 'integer'],
            [['logged_date'], 'safe'],
            //[['process_stage_answer'], 'string', 'max' => 128],
            [['comment'], 'string', 'max' => 256],
            ['logged_by', 'default', 'value' => \yii::$app->user->id],
            ['status', 'default', 'value' => 1],
            [['location'], 'string', 'max' => 32],
            [['device'], 'string', 'max' => 16]
        ];
    }
    
    public function fields() {
        return [
            'id',
            'tag_id',
            'stageInfo',
            'answer',
            'loggedBy',
            'comment',
            'location',
            'logged_by',
            'device',
            'logged_date' => function() {
                return date("d M Y H:i:s", strtotime($this->logged_date));
            }
        ];
    }
    
    public static function find()
    {
        $query = parent::find()->andWhere(['company_id' => \yii::$app->user->identity->company_id])->joinWith("user")->andWhere(['tag_activity_log.status' => 1])->orderBy("logged_date DESC");
        
        return $query;
    }
    
    public function extraFields() {
        return [
                'attachments',
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
            'process_stage_id' => 'Process Stage ID',
            'process_stage_answer' => 'Process Stage Answer',
            'comment' => 'Comment',
            'location' => 'Location',
            'device' => 'Device',
            'status' => 'Status',
            'logged_by' => 'Logged By',
            'logged_date' => 'Logged Date',
        ];
    }
    
    public function getAttachments()
    {
        return $this->hasMany(TagActivityAttachment::className(), ['activity_log_id' => 'id']);
    }
    
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'logged_by']);
    }
	
    public function getStageInfo(){
        return $this->hasOne(TagProcess::className(),['id' => 'process_stage_id']);
    }
    
    public function getAnswer(){
        return $this->hasOne(TagProcess::className(),['id' => 'process_stage_answer']);
    }
    
    public function getLoggedBy(){
        return $this->hasOne(User::className(),['id' => 'logged_by']);
    }
    
    public function actDelete() {
        $this->status = 2;
        return $this->save();
    }    
}
