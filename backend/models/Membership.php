<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "membership".
 *
 * @property integer $id
 * @property string $name
 * @property integer $limit_active_projects
 * @property integer $limit_tags
 * @property integer $limit_users
 * @property integer $limit_data
 * @property integer $limit_items
 * @property integer $status
 * @property string $modified_date
 */
class Membership extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'membership';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'limit_data', 'limit_items', 'status'], 'required'],
            [['limit_active_projects', 'limit_tags', 'limit_users', 'limit_data', 'limit_items', 'status'], 'integer'],
            ['name', 'unique'],
            ['status', 'validateStatus'],
            [['modified_date'], 'safe'],
            [['name'], 'string', 'max' => 128]
        ];
    }
    
    /**
     * Validates the status.
     * This method serves as the inline validation for status field.
     * System would not allow disabling the status if company exists
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateStatus($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if($this->$attribute == 0) {
                $companyObj = Company::findOne(['membership_id' => $this->id]);

                if($companyObj) {
                    $this->addError($attribute, 'You can\'t disable this membership since, there are one or more companies with this membership assigned.');
                    return;
                }
            }
            else if($this->$attribute == 1) {
                $companyObj = Company::findOne(['membership_id' => $this->id]);

                if($companyObj) {
                    $this->addError($attribute, "This membership can't be deleted since there are one or more companies are assigned.");
                    return;
                }
            }
        }
    }
    
    public static function find() {
        return parent::find()->andWhere(['<>', 'status', 2]);
    }
    
    public function fields() {
        return [
            'id',
            'name',
            'limit_active_projects',
            'limit_tags',
            'limit_users',
            'limit_data',
            'limit_items',
            'status'
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'limit_active_projects' => 'Limit Active Projects',
            'limit_tags' => 'Limit Tags',
            'limit_users' => 'Limit Users',
            'limit_data' => 'Limit Data',
            'limit_items' => 'Limit Items',
            'status' => 'Status',
            'modified_date' => 'Modified Date',
        ];
    }
    
    public function actDelete() {
        
        $companyObj = Company::findOne(['membership_id' => $this->id]);
        
        if($companyObj) {
            return "This membership can't be deleted since there are one or more companies are assigned.";
        }
        
        $this->status = 2;
        $return = $this->save(FALSE);
        return $return;
    }
}
