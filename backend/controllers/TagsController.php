<?php
namespace backend\controllers;

use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
/**
 * Class TagsController
 * @package rest\versions\v1\controllers
 */
class TagsController extends ApiController
{
    public $partialMatchFields;
        
    public function init() {
        $this->modelClass = 'backend\models\Tags';
        
        $this->partialMatchFields = ['tag_name', 'tag_description', 'uid', 'product_code'];
        
        parent::init();
    }
    
    public function actionSearch() {
        if (!$_POST) {
            
            $_GET['expand'] = "project_level, itemDetails, userGroup";
            
            $post = \Yii::$app->request->post();
            
            $model = new $this->modelClass;
            
            $query = $model->find();
            
            if(isset($post['select']))
               $query->select($post['select']);

            if(isset($post['search'])) {
                foreach($post['search'] as $key => $val)
                    if($key=="date_range") {
                        if(isset($val['from_date']) && isset($val['to_date'])) {
                            $val['from_date'] = date("Y-m-d H:i:s", strtotime($val['from_date']));
                            $val['to_date'] = date("Y-m-d", strtotime($val['to_date']));
                            $query->andWhere(['between', 'created_date', $val['from_date'], $val['to_date']]);
                        }
                        else if(isset($val['from_date'])) {
                            $val['from_date'] = date("Y-m-d", strtotime($val['from_date']));
                            $query->andWhere(['>=', 'created_date', $val['from_date']]);
                        }
                        else if(isset($val['to_date'])) {
                            $val['to_date'] = date("Y-m-d", strtotime($val['to_date']));
                            $query->andWhere(['<=', 'created_date', $val['to_date']]);
                        }
                    }
                    else if(is_array ($val)) {
                        if(isset($val['project']))
                            $query->where(['project_id' => $val['project']['id']]);
                    }
                    else if(isset($val)) {
                        if(in_array($key, $this->partialMatchFields))
                            $query->andWhere(['like', $key, $val]);
                        else
                            $query->where([$key => $val]);
                    }
            }
            $pageLimit = 20;
            if(isset($post['sort']))
                $_GET['sort'] = $post['sort'];
            if(isset($post['page']))
                $_GET['page'] = $post['page'];
            if(isset($post['limit']))
                $pageLimit = $post['limit'];
            
            try {
                $provider = new ActiveDataProvider ([
                    'query' => $query,
                    'pagination'=>array(
                        'pageSize'=>$pageLimit
                    ),
                ]);
            } catch (Exception $ex) {
                throw new \yii\web\HttpException(500, 'Internal server error');
            }
            return $provider;
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
    }
    
    public function actionGetall() {
        $_GET['expand'] = "project_level, itemDetails, userGroup";
        return parent::actionGetall();
    }
    
    public function actionCreateSimpleTags() {
        if (!$_POST) {
            
            $post = \yii::$app->request->post("tagDetails");
		
            $models = $this->loadMultiple($post);
            $company_id = \yii::$app->user->identity->company_id;

            $validate = $this->validateMultiple($models);

            if (!count($validate)) {
                $hasError = false;
                foreach ($models as $key => $tag) {
                    if ($tag->save()) {
                        // do something here after saving
                        $validate[$key]['id'] = $tag->id;
                        
                        foreach($post['tag_assignment'] as $i => $v) {
                            unset($temp);
                            unset($tagAssignmentModel);
                            $temp['user_id'] = (int) $v['user_id'];
                            $temp['process_stage_from'] = (int) $v['process_stage_from']['id'];
                            $temp['process_stage_to'] = (int) $v['process_stage_to']['id'];
                            $temp['mandatory'] = (int) $v['mandatory'];
                            $notification_status = [];
                            foreach($v['notification_status'] as $status)
                                $notification_status[] = $status['id'];

                            $temp['notification_status'] = implode(",", $notification_status);

                            $temp['notification_frequency'] = $v['notification_frequency']['id'];

                            $tagAssignmentModel = new \backend\models\TagAssignment();

                            $tagAssignmentModel->setAttributes($temp);

                            $tag->link("tagAssignment", $tagAssignmentModel);
                        }
                    }
                    else {
                        $hasError = true;
                        \yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed.');   
                        
                        foreach ($tag->getErrors() as $attribute => $errors) {
                            $validate[$key][$attribute] = $errors;
                        }
                    }
                }
                if($hasError)
                    return $validate;
                else
                    return "Success";
            }

            \yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed.');               

            return $validate;
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
    }
    
    public static function validateMultiple($models, $attributes = null)
    {
        $result = [];
        /* @var $model Model */
        foreach ($models as $i => $model) {
            $model->validate($attributes);
            foreach ($model->getErrors() as $attribute => $errors) {
                $result[$i][$attribute] = $errors;
            }
        }
        
        return $result;
    }
    
	
    /**
     * Populates a set of models with the data from end user.
     * @return boolean whether the model is successfully populated with some data.
     */
    public function loadMultiple($data)
    {
        $models = [];
        $company_id = \yii::$app->user->identity->company_id;
        
        $temp['notification_status'] = [];
        
        foreach ($data['tags'] as $i => $tag) {
            $models[$i] = new $this->modelClass;
            if(isset($tag['id']) && $tag['id']>0) {
                $existingTag = $models[$i]->find(['id' => $tag['id'], 'status' => self::STATUS_ACTIVE])->one();
                if($existingTag)
                    $models[$i] = $existingTag;
            }
            
            if(isset($tag['id']) && $tag['id'] > 0)
                $models[$i]->uid = static::generateUID(10);
            
            $models[$i]->type = "sT";
            $models[$i]->project_id = $data['project_id'];
            $models[$i]->tag_name = $tag['pre'].$tag['tagName'].$tag['post'];
            $models[$i]->tag_description = $tag['tagDescription'];
            $models[$i]->product_code = $tag['productCode'];
            $models[$i]->user_group_id = $data['user_group_id'];
            $models[$i]->project_level_id = $data['project_level_id'];
            $models[$i]->tag_item_id = $data['tag_item_id'];
            $models[$i]->tag_process_flow_id = $data['tag_process_flow_id'];
        }
        return $models;
    }
}
