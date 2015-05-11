<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "tag_items".
 *
 * @property integer $id
 * @property integer $company_id
 * @property string $item_name
 * @property integer $parent_id
 * @property integer $status
 * @property integer $created_by
 * @property string $created_date
 */
class Items extends \yii\db\ActiveRecord
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 1;
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
            [['item_name', 'parent_id'], 'required'],
            [['company_id', 'parent_id', 'status', 'created_by'], 'integer'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['company_id', 'default', 'value' => \yii::$app->user->identity->company_id],
            ['created_by', 'default', 'value' => \yii::$app->user->id],
            ['created_date', 'default', 'value' => date("Y-m-d H:i:s")],
            [['created_date'], 'safe'],
            [['item_name'], 'string', 'max' => 256]
        ];
    }
    
    public function fields()
    {
        $post = \Yii::$app->request->post();
        
        if(isset($post['select']))
           return $post['select'];
                
        return [
            'id',
            'item_name',
            'parent_id',
            'status',
        ];
    }
    
    public static function find()
    {
        $query = parent::find()->where(['tag_items.company_id' => \yii::$app->user->identity->company_id, 'tag_items.status' => 1])
            ->joinWith("projectIds")->groupBy("tag_items.id");
        
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
            'created_by' => 'Create By',
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
    
    public function getParent()
    {
        return $this->hasMany(Items::className(), ['id' => 'parent_id'])->from(['parent' => Items::tableName()]);
    }
    
    public function actDelete() {
        
        $hasActiveTag = false;
        
        $getChild = $this->find()->andWhere(['parent_id' => $this->id])->all();
        
        foreach($getChild as $childItem) {
            $subChildItems = \yii\helpers\ArrayHelper::getColumn($this->find()->andWhere(['parent_id' => $childItem->id])->all(), "id", false);
            $subChildItems[] = $childItem->id;
            
            if(count($subChildItems) > 0) {
                $getTags = Tags::find()->andWhere(['tag_item_id' => $subChildItems, 'tag_status' => 1])->andWhere(['<>', 'completed', 1])->one();

                if($getTags) {
                    $hasActiveTag = true;
                    break;
                }
            }
        }
        if(!$hasActiveTag) {
            $getTags = Tags::find()->andWhere(['tag_item_id' => $this->id, 'tag_status' => 1])->andWhere(['<>', 'completed', 1])->one();
            if($getTags) {
                $hasActiveTag = true;
            }
        }
        
        if($hasActiveTag) {
            $this->addError("status", "This item can't be deleted, because there are more than 1 active tags available using this item.");
        }
        
        if(!$this->hasErrors()) {
            $this->status = 2;
            return $this->save();
        }
    }
}
