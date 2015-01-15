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
        ];
    }
    
    // default scope to check company_id
    public static function find()
    {
$query = parent::find()->where(['company_id' => \yii::$app->user->identity->company_id])->andWhere(['<>', 'status', '2']);
        
        return $query;
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
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }
    
    public function actDelete() {
        $this->status = 2;
        return $this->save();
    }
}
