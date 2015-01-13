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
    
    private static function getMenuRecrusive($parent)
    {

        $items = static::find()
            ->where(['parent_id' => $parent])
            ->all();

        $result = []; 

        foreach ($items as $item) {
            
            $child = static::getMenuRecrusive($item->id);
            
            $linkOptions = [];
            
            if($child)
                $linkOptions = ["ng-disable" => "true", 'ng-href' => ""];
                    
            $result[] = [
                    'label' => $item->label,
                    'url' => [$item->url],
                    'items' => $child,
                    'linkOptions' => $linkOptions
                ];
        }
        return ($result?$result:null);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjectIds()
    {
        return $this->hasMany(ItemsProjects::className(), ['item_id' => 'id']);
    }
    
    public function actDelete() {
        $this->status = 2;
        return $this->save();
    }
}
