<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "menu".
 *
 * @property integer $id
 * @property string $label
 * @property string $url
 * @property integer $parent_id
 * @property integer $status
 */
class Menu extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'menu';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['label', 'url', 'position'], 'required'],
            [['parent_id', 'status'], 'integer'],
            ['parent_id', 'default', 'value' => '0'],
            [['label'], 'string', 'max' => 128],
            [['url'], 'string', 'max' => 256]
        ];
    }
    
    public static function find() {
        
        if(!\yii::$app->user->isGuest && \yii::$app->session->get('user.role_details')->type) {
            return parent::find()->andWhere(['type' => \yii::$app->session->get('user.role_details')->type]);
        }
        else
            return parent::find();
    }
    
    public static function getMenu()
    {
        $result = static::getMenuRecrusive(0);
        return $result;
    }

    private static function getMenuRecrusive($parent)
    {
        
        $items = static::find()
            ->andWhere(['parent_id' => $parent, 'status' => 1])
            ->orderBy("position")
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
    
    public function getParent()
    {
        return $this->hasOne(static::className(), ['id' => 'parent_id']);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'label' => 'Label',
            'url' => 'Url',
            'parent_id' => 'Parent ID',
            'status' => 'Status',
        ];
    }
}
