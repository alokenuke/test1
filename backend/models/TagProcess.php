<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "tag_process".
 *
 * @property integer $id
 * @property integer $type
 * @property string $process_name
 * @property integer $company_id
 * @property integer $status
 * @property integer $parent_id
 * @property integer $created_by
 * @property string $created_date
 */
class TagProcess extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tag_process';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'company_id', 'status', 'parent_id', 'created_by'], 'integer'],
            [['process_name', 'company_id', 'status', 'parent_id', 'created_by'], 'required'],
            [['created_date'], 'safe'],
            [['process_name'], 'string', 'max' => 128]
        ];
    }
    
    public static function find()
    {
        $query = parent::find()->where(['tag_process.company_id' => \yii::$app->user->identity->company_id, 'status' => 1])
            ->joinWith("projectIds");
                return $query;
    }

    public function fields() {
        return [
            'id',
            'process_name',
            'status'
        ];
    }
    
    public function extraFields() {
        return [
            'parentProcess'
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'process_name' => 'Process Name',
            'company_id' => 'Company ID',
            'status' => 'Status',
            'parent_id' => 'Parent ID',
            'created_by' => 'Created By',
            'created_date' => 'Created Date',
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjectIds()
    {
        return $this->hasMany(TagProcessProjects::className(), ['tag_process_id' => 'id']);
    }
    
    public function actDelete() {
        $this->status = 2;
        return $this->save();
    }
    public function getParentProcess() {
        return $this->hasOne(TagProcess::className(), ['id' => 'parent_id']);
    }
}
