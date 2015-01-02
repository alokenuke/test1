<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tag_process_projects".
 *
 * @property integer $tag_process_id
 * @property integer $project_id
 * @property integer $created_by
 * @property string $created_date
 */
class TagProcessProjects extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tag_process_projects';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tag_process_id', 'project_id', 'created_by'], 'required'],
            [['tag_process_id', 'project_id', 'created_by'], 'integer'],
            [['created_date'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tag_process_id' => 'Tag Process ID',
            'project_id' => 'Project ID',
            'created_by' => 'Created By',
            'created_date' => 'Created Date',
        ];
    }
}
