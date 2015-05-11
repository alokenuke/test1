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
    public $flagHierarchy, $flagDefault, $flagCompletion;
    
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
            [['type', 'parent_id', 'option_type'], 'integer'],
            [['created_date'], 'safe'],
            ['params', 'string'],
            [['process_name'], 'string', 'max' => 128],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['company_id', 'default', 'value' => \yii::$app->user->identity->company_id],
            ['created_by', 'default', 'value' => \yii::$app->user->id],
            [['option_type', 'created_date'], 'safe'],
        ];
    }
    
    public static function find()
    {
        $post = \Yii::$app->request->post();
        
        $select = ['tag_process.id', 'tag_process.process_name', 'tag_process.parent_id', 'tag_process.type', 'tag_process.params', 'tag_process.option_type', 'tag_process.position'];

        if(isset($post['select']['Process']))
           $select = $post['select']['Process'];

        $query = parent::find()->select($select)->where(['tag_process.company_id' => \yii::$app->user->identity->company_id, 'tag_process.status' => 1])->joinWith("projectIds");
        
        return $query;
    }
    
    public function fields() {
        $this->params = (array) json_decode($this->params);
        return [
            'id',
            'process_name',
            'flagDefault' => function() {
                if(isset($this->params['flagDefault']))
                    return $this->params['flagDefault'];
            },
            'flagCompletion' => function() {
                if(isset($this->params['flagCompletion']))
                    return $this->params['flagCompletion'];
            },
            'flagHierarchy' => function() {
                if(isset($this->params['flagHierarchy']))
                    return $this->params['flagHierarchy'];
            },
            'type',
            'option_type',
            'parent_id',
            'position',
            'status'
        ];
    }
    
    public function extraFields() {
        return [
            'parentProcess',
            'tree' => function() {
                return static::getTreeRecrusive($this->id);
            },
            'childOptions' => function() {
                if($this->type==2)
                    return static::getStageOptions($this->id);
            },
            'checkProcessError' => function() {
                $return = false;
                if($this->type==1) {
                    // Select all process stages and check related options
                    $processStages = static::find()->andWhere(['parent_id' => $this->id])->all();
                    
                    foreach ($processStages as $process)
                    {
                        // If stage is of status % or textbox do not check child options
                        if($process->option_type==3 || $process->option_type==5)
                            continue;
                        
                        // else check the params for default and completion flags.
                        $params = (array) json_decode($process->params);
                        
                        // check if default and completion both flags are available.
                        if(!(isset($params['flagDefault']) && $params['flagDefault'] >0) || !(isset($params['flagCompletion']) && $params['flagCompletion'] >0)) {
                            $return = true;
                            break;
                        }
                        else {
                            // check if system has more than 1 options available
                            $optionsCount = static::find()->andWhere(['parent_id' => $process->id])->count();
                            if($optionsCount < 2) {
                                $return = true;
                                return;
                            }
                        }
                    }
                }
                return $return;
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
                'option_type' => $item->option_type,
                'tree' => ($child?$child:[]),
                'parent_id' => $item->parent_id
            ];
        }
        return $result;
    }
    
    
    private static function getStageOptions($parent)
    {
        $items = static::find()
            ->andWhere(['parent_id' => $parent])
            ->andWhere(['type' => 3])
            ->orderBy("position")
            ->all();
        
        $result = []; 

        foreach ($items as $item) {
            
            $params = (array) json_decode($item->params);
            
            $result[] = [
                'id' => $item->id,
                'process_name' => $item->process_name,
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
        $hasActiveTag = false;
        
        $process_id = 0;
        if($this->type==0) {
            $processFlowObj = $this->find()->andWhere(['parent_id' => $this->id])->all();
            $process_id = \yii\helpers\ArrayHelper::getColumn($processFlowObj, "id" ,false);
        }
        else if($this->type==1)
            $process_id = $this->id;
        else if($this->type==2)
            $process_id = $this->parent_id;
        else if($this->type==3)
        {
            $processStage = $this->find()->andWhere(['id' => $this->parent_id])->one();
            $process_id = $processStage->parent_id;
        }
        
        $hasActiveTag = Tags::find()->andWhere(['tag_process_flow_id' => $process_id, 'tag_status' => 1])->andWhere(['<>', 'completed', 1])->one();
        
        if($hasActiveTag) {
            $this->addError("status", "This process can't be deleted, because there are more than 1 active tags available using this process.");
        }
        
        if(!$this->hasErrors()) {
            $this->status = 2;
            return $this->save();
        }
    }
    public function getParentProcess() {
        return $this->hasOne(TagProcess::className(), ['id' => 'parent_id']);
    }
}
