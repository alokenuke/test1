<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "company".
 *
 * @property integer $id
 * @property string $company_name
 * @property integer $company_owner
 * @property string $company_logo
 * @property integer $company_status
 * @property string $expiry_date
 * @property string $created_date
 */
class Company extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'company';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_name', 'company_owner', 'company_logo', 'company_status', 'expiry_date'], 'required'],
            [['company_status'], 'integer'],
            [['expiry_date', 'created_date'], 'safe'],
            [['company_name'], 'string', 'max' => 256],
            [['company_logo'], 'string', 'max' => 128]
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_name' => 'Company Name',
            'company_owner' => 'Company Owner',
            'company_logo' => 'Company Logo',
            'company_status' => 'Company Status',
            'expiry_date' => 'Expiry Date',
            'created_date' => 'Created Date',
        ];
    }
}
