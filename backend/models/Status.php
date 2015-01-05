<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "status".
 *
 * @property integer $id
 * @property string $label
 * @property string $type
 * @property integer $status
 * @property string $created_on
 */
class Status extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'status';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['label', 'type', 'status'], 'required'],
            [['status'], 'integer'],
            [['created_on'], 'safe'],
            [['label', 'type'], 'string', 'max' => 128]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'label' => 'Label',
            'type' => 'Type',
            'status' => 'Status',
            'created_on' => 'Created On',
        ];
    }
}
