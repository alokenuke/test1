<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tag_items".
 *
 * @property integer $id
 * @property integer $company_id
 * @property string $item_name
 * @property integer $parent_id
 * @property integer $status
 * @property integer $create_by
 * @property string $created_date
 */
class Items extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tag_items';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'item_name', 'parent_id', 'status', 'create_by'], 'required'],
            [['company_id', 'parent_id', 'status', 'create_by'], 'integer'],
            [['created_date'], 'safe'],
            [['item_name'], 'string', 'max' => 256]
        ];
    }
    
    public static function find()
    {
        $query = parent::find()->where(['company_id' => \yii::$app->user->identity->company_id, 'status' => 1])
            ->joinWith("projectIds");
        
        $post = \Yii::$app->request->post();
        
        $select = "*";
        
        if(isset($post['select']))
           $select = $post['select'];
        
        $query->select($select);
        
        if(isset($post['search'])) {
            foreach($post['search'] as $key => $val)
                if(isset($val))
                    $query->andWhere([$key => $val]);
        }
        
        return $query;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Company ID',
            'item_name' => 'Item Name',
            'parent_id' => 'Parent ID',
            'status' => 'Status',
            'create_by' => 'Create By',
            'created_date' => 'Created Date',
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjectIds()
    {
        return $this->hasMany(ItemsProjects::className(), ['item_id' => 'id']);
    }
}
