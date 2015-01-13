<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "project_level".
 *
 * @property integer $id
 * @property integer $company_id
 * @property string $level_name
 * @property integer $parent_id
 * @property integer $project_id
 * @property integer $status
 * @property integer $created_by
 * @property string $created_date
 *
 * @property Tags[] $tags
 */
class ProjectLevel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'project_level';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'level_name', 'project_id', 'status', 'created_by'], 'required'],
            [['company_id', 'parent_id', 'project_id', 'status', 'created_by'], 'integer'],
            [['created_date'], 'safe'],
            [['level_name'], 'string', 'max' => 256]
        ];
    }
    
    public static function find()
    {
        $query = parent::find()->where(['company_id' => \yii::$app->user->identity->company_id, 'status' => 1]);
        
        return $query;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Company ID',
            'level_name' => 'Level Name',
            'parent_id' => 'Parent ID',
            'project_id' => 'Project ID',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_date' => 'Created Date',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTags()
    {
        return $this->hasMany(Tags::className(), ['project_level_id' => 'id']);
    }
    
    public function actDelete() {
        $this->status = 2;
        return $this->save();
    }
}
