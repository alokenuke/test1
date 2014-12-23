<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tag_process_stages".
 *
 * @property integer $id
 * @property integer $type_id
 * @property string $stage_name
 * @property integer $status
 * @property integer $created_by
 * @property string $created_date
 */
class TagProcessStages extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tag_process_stages';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type_id', 'stage_name', 'status', 'created_by'], 'required'],
            [['type_id', 'status', 'created_by'], 'integer'],
            [['created_date'], 'safe'],
            [['stage_name'], 'string', 'max' => 256]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type_id' => 'Type ID',
            'stage_name' => 'Stage Name',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_date' => 'Created Date',
        ];
    }
}
