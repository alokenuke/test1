<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tags".
 *
 * @property integer $id
 * @property string $site_name
 * @property string $site_component
 * @property string $area
 * @property string $location
 * @property string $sub_location
 * @property string $task_status
 * @property string $unique_code
 * @property string $about_task
 * @property string $form_type
 * @property integer $id_form_type
 * @property integer $timezone_id
 * @property integer $user_id
 * @property integer $project_id
 * @property string $same_task
 * @property string $created
 * @property string $modified
 */
class Tags extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tags';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['task_status', 'unique_code', 'created', 'modified'], 'required'],
            [['about_task'], 'string'],
            [['id_form_type', 'timezone_id', 'user_id', 'project_id'], 'integer'],
            [['created', 'modified'], 'safe'],
            [['site_name', 'site_component', 'area', 'location', 'sub_location'], 'string', 'max' => 250],
            [['task_status', 'form_type'], 'string', 'max' => 100],
            [['unique_code', 'same_task'], 'string', 'max' => 200]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'site_name' => 'Site Name',
            'site_component' => 'Site Component',
            'area' => 'Area',
            'location' => 'Location',
            'sub_location' => 'Sub Location',
            'task_status' => 'Task Status',
            'unique_code' => 'Unique Code',
            'about_task' => 'About Task',
            'form_type' => 'Form Type',
            'id_form_type' => 'Id Form Type',
            'timezone_id' => 'Timezone ID',
            'user_id' => 'User ID',
            'project_id' => 'Project ID',
            'same_task' => 'Same Task',
            'created' => 'Created',
            'modified' => 'Modified',
        ];
    }
}
