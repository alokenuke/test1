<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tag_item_group".
 *
 * @property integer $id
 * @property integer $company_id
 * @property string $item_type_name
 * @property integer $status
 * @property integer $create_by
 * @property string $created_date
 */
class TagItemGroup extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tag_item_group';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'item_type_name', 'status', 'create_by'], 'required'],
            [['company_id', 'status', 'create_by'], 'integer'],
            [['created_date'], 'safe'],
            [['item_type_name'], 'string', 'max' => 256]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Company ID',
            'item_type_name' => 'Item Type Name',
            'status' => 'Status',
            'create_by' => 'Create By',
            'created_date' => 'Created Date',
        ];
    }
}
