<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tag_process_type".
 *
 * @property integer $id
 * @property string $process_name
 * @property integer $company_id
 * @property integer $status
 * @property integer $created_by
 * @property string $created_date
 */
class TagProcessType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tag_process_type';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['process_name', 'company_id', 'status', 'created_by'], 'required'],
            [['company_id', 'status', 'created_by'], 'integer'],
            [['created_date'], 'safe'],
            [['process_name'], 'string', 'max' => 128]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'process_name' => 'Process Name',
            'company_id' => 'Company ID',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_date' => 'Created Date',
        ];
    }
}
