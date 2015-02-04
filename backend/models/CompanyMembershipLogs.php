<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "company_membership_logs".
 *
 * @property integer $id
 * @property integer $company_id
 * @property integer $membership_id
 * @property integer $expiry_date
 * @property integer $created_by
 * @property integer $created_date
 */
class CompanyMembershipLogs extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'company_membership_logs';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'membership_id', 'expiry_date', 'created_by', 'created_date'], 'required'],
            [['company_id', 'membership_id', 'expiry_date', 'created_by', 'created_date'], 'integer']
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
            'membership_id' => 'Membership ID',
            'expiry_date' => 'Expiry Date',
            'created_by' => 'Created By',
            'created_date' => 'Created Date',
        ];
    }
}
