<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "tag_assignment".
 *
 * @property integer $id
 * @property integer $tag_id
 * @property integer $user_id
 * @property integer $from_process
 * @property integer $to_process
 * @property integer $mandatory
 * @property integer $status
 * @property string $created_on
 */
class TagAssignment extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tag_assignment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tag_id', 'user_id', 'from_process', 'to_process', 'mandatory', 'status'], 'required'],
            [['tag_id', 'user_id', 'from_process', 'to_process', 'mandatory', 'status'], 'integer'],
            [['created_on'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tag_id' => 'Tag ID',
            'user_id' => 'User ID',
            'from_process' => 'From Process',
            'to_process' => 'To Process',
            'mandatory' => 'Mandatory',
            'status' => 'Status',
            'created_on' => 'Created On',
        ];
    }
    
    public function actDelete() {
        $this->status = 2;
        return $this->save();
    }
}
