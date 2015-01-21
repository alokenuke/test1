<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "tag_user_notification_status".
 *
 * @property integer $tag_id
 * @property integer $tag_assignment_id
 * @property integer $process_stage_id
 */
class TagUserNotificationStatus extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tag_user_notification_status';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tag_id', 'tag_assignment_id', 'process_stage_id'], 'required'],
            [['tag_id', 'tag_assignment_id', 'process_stage_id'], 'integer']
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tag_id' => 'Tag ID',
            'tag_assignment_id' => 'Tag Assignment ID',
            'process_stage_id' => 'Process Stage ID',
        ];
    }
}
