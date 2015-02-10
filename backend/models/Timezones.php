<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "countries".
 *
 * @property integer $id
 * @property string $country_code
 * @property string $country_name
 */
class Timezones extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'timezones';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'string', 'max' => 2],
            [['name'], 'string', 'max' => 100]
        ];
    }
    
    public static function find() {
        return parent::find()->andWhere(['status' => 1]);
    }
    
    public function fields() {
        return [
            'id',
            'name'
        ];
    }
    
    public function actDelete() {
        $this->status = 2;
        return $this->save();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Timezone Name',
        ];
    }
}
