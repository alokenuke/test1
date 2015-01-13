<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "user_group_projects".
 *
 * @property integer $id
 * @property integer $user_group_id
 * @property integer $project_id
 * @property integer $assigned_by
 * @property string $assigned_date
 */
class UserGroupProjects extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_group_projects';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_group_id', 'project_id', 'assigned_by'], 'required'],
            [['user_group_id', 'project_id', 'assigned_by'], 'integer'],
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
            'user_group_id' => 'User Group ID',
            'project_id' => 'Project ID',
            'assigned_by' => 'Assigned By',
            'assigned_date' => 'Assigned Date',
        ];
    }
    
    public function actDelete() {
        return $this->delete();
    }
}
