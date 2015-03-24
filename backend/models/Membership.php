<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "membership".
 *
 * @property integer $id
 * @property string $name
 * @property integer $limit_active_projects
 * @property integer $limit_tags
 * @property integer $limit_users
 * @property integer $limit_data
 * @property integer $limit_items
 * @property integer $status
 * @property string $modified_date
 */
class Membership extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'membership';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'limit_data', 'limit_items', 'status'], 'required'],
            [['limit_active_projects', 'limit_tags', 'limit_users', 'limit_data', 'limit_items', 'status'], 'integer'],
            ['name', 'unique'],
            [['modified_date'], 'safe'],
            [['name'], 'string', 'max' => 128]
        ];
    }
    
    public function fields() {
        return [
            'id',
            'name',
            'limit_active_projects',
            'limit_tags',
            'limit_users',
            'limit_data',
            'limit_items',
            'status'
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'limit_active_projects' => 'Limit Active Projects',
            'limit_tags' => 'Limit Tags',
            'limit_users' => 'Limit Users',
            'limit_data' => 'Limit Data',
            'limit_items' => 'Limit Items',
            'status' => 'Status',
            'modified_date' => 'Modified Date',
        ];
    }
}
