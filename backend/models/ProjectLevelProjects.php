<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "project_level_projects".
 *
 * @property integer $id
 * @property integer $level_id
 * @property integer $project_id
 * @property integer $assigned_by
 * @property string $assigned_date
 */
class ProjectLevelProjects extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'project_level_projects';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['level_id', 'project_id', 'assigned_by'], 'required'],
            [['level_id', 'project_id', 'assigned_by'], 'integer'],
            [['assigned_date'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'level_id' => 'Level Group ID',
            'project_id' => 'Project ID',
            'assigned_by' => 'Assigned By',
            'assigned_date' => 'Assigned Date',
        ];
    }
}
