<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "tag_activity_attachment".
 *
 * @property integer $id
 * @property integer $tag_id
 * @property integer $activity_log_id
 * @property string $filename
 * @property string $file_type
 * @property integer $status
 * @property integer $created_by
 * @property string $created_date
 */
class TagActivityAttachment extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tag_activity_attachment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tag_id', 'activity_log_id', 'filename', 'file_type', 'status', 'created_by'], 'required'],
            [['tag_id', 'activity_log_id', 'status', 'created_by'], 'integer'],
            [['created_date'], 'safe'],
            [['filename'], 'string', 'max' => 128],
            [['file_type'], 'string', 'max' => 32]
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
            'activity_log_id' => 'Activity Log ID',
            'filename' => 'Filename',
            'file_type' => 'File Type',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_date' => 'Created Date',
        ];
    }
    
    public function fields() {
        return[
            'filename',
            'id'
        ];
    }
}
