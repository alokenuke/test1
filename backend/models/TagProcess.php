<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "tag_process".
 *
 * @property integer $id
 * @property integer $type
 * @property string $process_name
 * @property integer $company_id
 * @property integer $status
 * @property integer $parent_id
 * @property integer $created_by
 * @property string $created_date
 */
class TagProcess extends \yii\db\ActiveRecord
{
    const STATUS_NOTACTIVE = 0;
    const STATUS_ACTIVE = 1;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tag_process';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['process_name', 'parent_id'], 'required'],
            [['type', 'parent_id'], 'integer'],
            [['created_date'], 'safe'],
            ['params', 'string'],
            [['process_name'], 'string', 'max' => 128],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['company_id', 'default', 'value' => \yii::$app->user->identity->company_id],
            ['created_by', 'default', 'value' => \yii::$app->user->id],
            [['created_date'], 'safe'],
        ];
    }
    
    public static function find()
    {
        $post = \Yii::$app->request->post();
        
        $select = ['tag_process.id', 'tag_process.process_name', 'tag_process.parent_id', 'tag_process.type', 'tag_process.params'];

        if(isset($post['select']['Process']))
           $select = $post['select']['Process'];

        $query = parent::find()->select($select)->where(['tag_process.company_id' => \yii::$app->user->identity->company_id, 'tag_process.status' => 1])->joinWith("projectIds");
        
        return $query;
    }

    public function fields() {
        $params = (array) json_decode($this->params);
        return [
            'id',
            'process_name',
            'flagDefault' => function() {
                if(isset($params['flagDefault']))
                    return $params['flagDefault'];
            },
            'flagCompletion' => function() {
                if(isset($params['flagCompletion']))
                    return $params['flagCompletion'];
            },
            'flagHierarchy' => function() {
                if(isset($params['flagHierarchy']))
                    return $params['flagHierarchy'];
            },
            'type',
            'parent_id',
            'status'
        ];
    }
    
    public function extraFields() {
        return [
            'parentProcess',
            'tree' => function() {
                return static::getTreeRecrusive($this->id);
            }
        ];
    }
    
    
    private static function getTreeRecrusive($parent)
    {
        $items = static::find()
            ->andWhere(['parent_id' => $parent])
            ->orderBy("position")
            ->all();
        
        $result = []; 

        foreach ($items as $item) {
            $child = static::getTreeRecrusive($item->id);
                        
            $linkOptions = [];
            
            $params = (array) json_decode($item->params);
            
            $result[] = [
                'id' => $item->id,
                'process_name' => $item->process_name,
                'flagDefault' => (isset($params['flagDefault'])?$params['flagDefault']:null),
                'flagCompletion' => (isset($params['flagCompletion'])?$params['flagCompletion']:null),
                'flagHierarchy' => (isset($params['flagHierarchy'])?$params['flagHierarchy']:null),
                'type' => $item->type,
                'tree' => ($child?$child:[]),
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
            'type' => 'Type',
            'process_name' => 'Process Name',
            'company_id' => 'Company ID',
            'status' => 'Status',
            'parent_id' => 'Parent ID',
            'created_by' => 'Created By',
            'created_date' => 'Created Date',
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjectIds()
    {
        return $this->hasMany(TagProcessProjects::className(), ['process_id' => 'id']);
    }
    
    public function actDelete() {
        $this->status = 2;
        return $this->save();
    }
    public function getParentProcess() {
        return $this->hasOne(TagProcess::className(), ['id' => 'parent_id']);
    }
}
