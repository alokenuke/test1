<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "rel_item_process".
 *
 * @property integer $company_id
 * @property integer $item_type_id
 * @property integer $process_flow_id
 * @property string $created_date
 */
class RelItemProcess extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rel_item_process';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['item_type_id', 'process_flow_id'], 'required'],
            [['company_id', 'item_type_id', 'process_flow_id'], 'integer'],
            ['company_id', 'default', 'value' => \yii::$app->user->identity->company_id],
            [['item_type_id', 'process_flow_id'], 'unique'],
            [['created_date'], 'safe']
        ];
    }
    
    public static function find()
    {
        $query = parent::find()->where(['rel_item_process.company_id' => \yii::$app->user->identity->company_id]);
        
        return $query;
    }
	public function extraFields() {
        return [
            'process'
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'company_id' => 'Company ID',
            'item_type_id' => 'Item Type ID',
            'process_flow_id' => 'Process Flow ID',
            'created_date' => 'Created Date',
        ];
    }

	public function getProcess(){ 
        return $this->hasOne(TagProcess::className(), ['id' => 'process_flow_id']);
    }
}
