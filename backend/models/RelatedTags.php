<?php

namespace backend\models;

use \Yii;


class RelatedTags extends \yii\db\ActiveRecord
{
       
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'related_tags';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['master_tag_id', 'tag_id',], 'required'],
            ['created_date', 'default', 'value' => date("Y-m-d H:i:s")],
            [['created_date'], 'safe'],
        ];
    }
    
    public function attributeLabels()
    {
        return [
            'master_tag_id',
            'tag_id',
            'created_date',
        ];
    }
    
    public function fields()
    {
        return [
            'master_tag_id',
            'tag_id',
            'created_date',
        ];
    }
    
}
