<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tag_items_projects".
 *
 * @property integer $item_id
 * @property integer $project_id
 * @property integer $created_by
 * @property string $created_date
 */
class ItemsProjects extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tag_items_projects';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['item_id', 'project_id', 'created_by'], 'required'],
            [['item_id', 'project_id', 'created_by'], 'integer'],
            [['created_date'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'item_id' => 'Item ID',
            'project_id' => 'Project ID',
            'created_by' => 'Created By',
            'created_date' => 'Created Date',
        ];
    }
}
