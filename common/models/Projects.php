<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "projects".
 *
 * @property integer $id
 * @property string $project_name
 * @property string $client_name
 * @property string $main_contractor
 * @property string $consultant
 * @property string $project_manager
 * @property string $project_location
 * @property string $city
 * @property string $country
 * @property string $head_office_address
 * @property string $project_director
 * @property integer $project_status
 * @property string $project_note
 * @property string $address
 * @property string $telephone
 * @property string $logo
 * @property integer $company_id
 * @property integer $created_by
 * @property integer $timezone_id
 * @property string $created_date
 * @property string $modified_date
 */
class Projects extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'projects';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_name', 'client_name', 'main_contractor', 'consultant', 'project_manager', 'project_location', 'city', 'country', 'head_office_address', 'project_director', 'company_id', 'created_by', 'created_date'], 'required'],
            [['project_status', 'company_id', 'created_by', 'timezone_id'], 'integer'],
            [['project_note'], 'string'],
            [['created_date', 'modified_date'], 'safe'],
            [['project_name', 'address', 'telephone'], 'string', 'max' => 200],
            [['client_name', 'main_contractor', 'consultant', 'project_manager', 'project_location', 'country', 'head_office_address', 'project_director'], 'string', 'max' => 255],
            [['city'], 'string', 'max' => 127],
            [['logo'], 'string', 'max' => 150]
        ];
    }
    
    public static function find()
    {
        return parent::find()->where(['company_id' => \yii::$app->user->identity->company_id, 'project_status' => 1]);
    }
    
    public function fields()
    {
        return [
            'id',
            'project_name',
            'client_name',
            'main_contractor',
            'consultant',
            'address',
            'project_location',
            'project_manager',
            'head_office_address',
            'project_status',
            'project_director',
            'city',
            'country'
        ];
    }
    
    public function getFormFields()
    {
        return [
            'project_name',
            'client_name',
            'main_contractor',
            'consultant',
            'project_location',
            'project_manager',
            'head_office_address',
            'project_status',
            'project_director',
            'city',
            'country'
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'project_name' => 'Project Name',
            'client_name' => 'Client Name',
            'main_contractor' => 'Main Contractor',
            'consultant' => 'Consultant',
            'project_manager' => 'Project Manager',
            'project_location' => 'Project Location',
            'city' => 'City',
            'country' => 'Country',
            'head_office_address' => 'Head Office Address',
            'project_director' => 'Project Director',
            'project_status' => 'Project Status',
            'project_note' => 'Project Note',
            'address' => 'Address',
            'telephone' => 'Telephone',
            'logo' => 'Logo',
            'company_id' => 'Company ID',
            'created_by' => 'Created By',
            'timezone_id' => 'Timezone ID',
            'created_date' => 'Created Date',
            'modified_date' => 'Modified Date',
        ];
    }
}
