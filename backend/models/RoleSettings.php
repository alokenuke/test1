<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "role_settings".
 *
 * @property integer $id
 * @property integer $role_id
 * @property string $role_params
 * @property string $module
 * @property integer $updated_by
 * @property string $update_date
 */
class RoleSettings extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'role_settings';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['role_id', 'role_params', 'module', 'updated_by'], 'required'],
            [['role_id', 'updated_by'], 'integer'],
            [['update_date'], 'safe'],
            [['role_params'], 'string', 'max' => 512],
            [['module'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'role_id' => 'Role ID',
            'role_params' => 'Role Params',
            'module' => 'Module',
            'updated_by' => 'Updated By',
            'update_date' => 'Update Date',
        ];
    }
}
