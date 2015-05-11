<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "roles
 * ".
 *
 * @property integer $id
 * @property string $uid
 * @property integer $project_id
 * @property string $tag_name
 * @property string $tag_description
 * @property integer $project_level_id
 * @property integer $user_group_id
 * @property string $product_code
 * @property integer $company_id
 * @property integer $tag_status
 * @property integer $created_by
 * @property string $created_date
 * @property string $modified_date
 *
 * @property Company $company
 * @property Projects $project
 * @property LevelFlow $projectLevel
 * @property UserGroups $userGroup
 */
class Roles extends \yii\db\ActiveRecord
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 1;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'roles';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['role_name', 'type','status'], 'required'],
            [['created_date', 'modified_date'], 'safe'],
            ['company_id', 'default', 'value' => \yii::$app->user->identity->company_id],
            ['created_date', 'default', 'value' => date("Y-m-d")],
            [['role_name'], 'unique', 'targetAttribute' => ['company_id', 'role_name'], 'message' => "You already have created this role!"],
        ];
    }
    
    // default scope to check company_id
    public static function find()
    {
        $query = parent::find()->andWhere(['<>', 'status', '2']);
        if(isset(\yii::$app->user->id) && Yii::$app->requestedRoute != "company/savecompany" && Yii::$app->requestedRoute != "roles/create")
            $query = $query->where(['company_id' => \yii::$app->user->identity->company_id]);
        
        if(Yii::$app->requestedRoute == "roles/update" || Yii::$app->requestedRoute == "roles/view")
            $query = $query->andWhere(["<>", 'isAdmin', 1]);
        
        if(Yii::$app->requestedRoute == "roles/getall")
            $query->andWhere(['status' => 1]);
        
        return $query;
    }
    
    public function afterSave($insert, $changedAttributes) {
        $moduleactions = \Yii::$app->request->post("moduleactions");
        if(isset($moduleactions)) {
            foreach($moduleactions as $module => $action) {
                $roleSettingsModel = RoleSettings::findOne(['role_id' => $this->id, 'module' => $module]);
                if(!$roleSettingsModel)
                    $roleSettingsModel = new RoleSettings();
                
                $roleSettingsModel->role_id = $this->id;
                $roleSettingsModel->module = $module;
                $roleSettingsModel->updated_by = \yii::$app->user->identity->id;
                
                $params = [];
                
                foreach($action as $act)
                    $params[$act['action']] = (isset ($act['isSelected']) && $act['isSelected']?$act['isSelected']:0);
                
                $roleSettingsModel->role_params = json_encode($params);
                
                $roleSettingsModel->save();
            }
        }
        parent::afterSave($insert, $changedAttributes);
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'role_name'=>'Role Name',
            'type'=>'Type',
            'company_id'=>'Company Id',
            'status'=>'Status',
            'created_date' => 'Created Date',
            'modified_date' => 'Modified Date',
            
        ];
    }
    
    public function fields()
    {
        return [
            'id',
            'type',            
            'role_name',
            'company',
            'status',
            'created_date',
        ];
    }
    
    public function extraFields() {
        return [
            'roleSettings' => function() {
                $actions = [];
                $model = new \backend\models\RoleSettings();
                
                foreach($model->findAll(['role_id' => $this->id]) as $data) {
                    $actions[$data->module] = json_decode($data->role_params);
                }
                if(count($actions))
                    return $actions;
            }
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }
    
    public function actDelete() {
        
        if($this->role_name=='Super Admin') {
            return "This user can't be removed.";
        }
        
        $this->status = 2;
        return $this->save();
    }
}
