<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "modules_actions".
 *
 * @property integer $id
 * @property integer $company_id
 * @property string $module_name
 * @property string $action
 * @property string $created_date
 */
class ModulesActions extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'modules_actions';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'module_name', 'action'], 'required'],
            [['company_id'], 'integer'],
            [['created_date'], 'safe'],
            [['module_name', 'action'], 'string', 'max' => 128]
        ];
    }
    
    public function getModuleActions($modules=[])
    {

        $result = [];
        if(!count($modules)) {
            $modules = static::find()
                ->andWhere(['status' => 1])
                ->andWhere(['company_id' => (\yii::$app->user->identity->company_id?1:0)])
                ->orderBy("module_name")
                ->groupBy("module_name")
                ->all();
        }
        foreach ($modules as $module) {
            
            if(isset($module->module_name))
                $module = $module->module_name;
            
            $actions = static::find()->andWhere(['status' => 1, 'module_name' => $module])->all();
            
            $result[$module] = $actions;
        }
        return ($result?$result:null);
    }
    
    // default scope to check company_id
    public static function find()
    {
        $query = parent::find()->andWhere(['status' => '1']);
        if(\yii::$app->user->identity->company_id > 0)
            $query = $query->andWhere(['company_id' => 1]);
        else
            $query = $query->andWhere(['company_id' => 0]);
        
        return $query;
    }
    
    public function fields() {
        return [
            'module_name',
            'action'
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
            'module_name' => 'Module Name',
            'action' => 'Action',
            'created_date' => 'Created Date',
        ];
    }
}
