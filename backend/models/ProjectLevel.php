<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "project_level".
 *
 * @property integer $id
 * @property integer $company_id
 * @property string $level_name
 * @property integer $parent_id
 * @property integer $status
 * @property integer $created_by
 * @property string $created_date
 *
 * @property Tags[] $tags
 */
class ProjectLevel extends \yii\db\ActiveRecord
{
    const STATUS_NOTACTIVE = 0;
    const STATUS_ACTIVE = 1;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'project_level';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['level_name', 'parent_id'], 'required'],
            [['company_id', 'parent_id', 'status', 'created_by'], 'integer'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['company_id', 'default', 'value' => \yii::$app->user->identity->company_id],
            ['created_by', 'default', 'value' => \yii::$app->user->id],
            [['created_date'], 'safe'],
            [['level_name'], 'string', 'max' => 256]
        ];
    }
    
    public static function find()
    {
        $post = \Yii::$app->request->post();
        
        $select = ['project_level.id', 'project_level.level_name', 'project_level.parent_id'];

        if(isset($post['select']['ProjectLevel']))
           $select = $post['select']['ProjectLevel'];

        $query = parent::find()->select($select)->where(['project_level.company_id' => \yii::$app->user->identity->company_id, 'project_level.status' => 1])->joinWith("projectIds");
        
        return $query;
    }
    
    public function fields()
    {
        return [
            'id',
            'level_name',
            'parent_id',
        ];
    }
    
    public function extraFields()
    {
        return [
            'levels' => function() {
                return static::getLevelRecrusive($this->id);
            }
        ];
    }
    
    private static function getLevelRecrusive($parent)
    {
        $items = static::find()
            ->where(['parent_id' => $parent])
            ->orderBy("position")
            ->all();
        
        $result = []; 

        foreach ($items as $item) {
            $child = static::getLevelRecrusive($item->id);
                        
            $linkOptions = [];
            
            $result[] = [
                'id' => $item->id,
                'level_name' => $item->level_name,
                'levels' => ($child?$child:[]),
                'parent_id' => $item->parent_id
            ];
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Company ID',
            'level_name' => 'Level Name',
            'parent_id' => 'Parent ID',
            'status' => 'Status',
            'created_by' => 'Created By',
            'created_date' => 'Created Date',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTags()
    {
        return $this->hasMany(Tags::className(), ['project_level_id' => 'id']);
    }
    
    public function actDelete() {
        $this->status = 2;
        return $this->save();
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjectIds()
    {
        return $this->hasMany(ProjectLevelProjects::className(), ['level_id' => 'id']);
    }
}
