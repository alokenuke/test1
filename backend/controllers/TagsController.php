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
    
    public function actionGetLastTag() {
        
        if (!$_POST) {
            $_GET['field'] = "id";
            
            $post = \yii::$app->request->post();
            
            if(isset($post['search']['project_id'])) {
                $projectId = $post['search']['project_id'];
                
                $model = new \backend\models\Tags();
                $data = $model->find()->andWhere(['project_id' => $projectId, 'tag_status' => 1, 'type' => 'sT'])->orderBy("created_date DESC")->one();
                
                if($data)
                    return $data->id;
                else
                    return 0;
                
            }
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
    }
    
    public function actionCreateSimpleTags() {
        if (!$_POST) {
            
            $post = \yii::$app->request->post("tagDetails");
            
            
            $connection = \Yii::$app->db;
            $transaction = $connection->beginTransaction();
		
            $models = $this->loadMultiple($post);
            $company_id = \yii::$app->user->identity->company_id;

            $validate = $this->validateMultiple($models);
            
            try {

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
                            
                            $temp['notification_frequency'] = $v['notification_frequency']['id'];

                            $tagAssignmentModel = new \backend\models\TagAssignment();

                            $tagAssignmentModel->setAttributes($temp);

                            $tag->link("tagAssignment", $tagAssignmentModel);
                            
                            foreach($v['notification_status'] as $status) {
                                if($status['id']=='all') {
                                    $temp['notification_status'] = "all";
                                    break;
                                }
                                else if($status['id']=='assigned') {
                                    $temp['notification_status'] = "assigned";
                                    break;
                                }
                                else {
                                    unset($tagNotificationStatus);
                                    $tagNotificationStatus = new \backend\models\TagUserNotificationStatus();
                                    $tagNotificationStatus->tag_id = $tag->id;
                                    $tagNotificationStatus->process_stage_id = $status['id'];
                                    
                                    $tagAssignmentModel->link("tagNotificationStatus", $tagNotificationStatus);
                                }
                            }
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
                if(!$hasError) {
                    $transaction->commit();
                    return "Success";
                }
            }

            \yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed.');
            $transaction->rollBack();
            return $validate;
            
            } catch (Exception $e) {
                $transaction->rollBack();
            }
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
    }
    
    public function actionUpdatesimpletags($id) {
        
        $model = \backend\models\Tags::findOne(['id' => $id]);
        
        if (!$_POST && $model) {
            
            $company_id = \yii::$app->user->identity->company_id;
            $post = \yii::$app->request->post("tagDetails");
            
            if(!$model->uid)
                $model->uid = $model->generateUID(10);
            $model->product_code = $post['product_code'];
            $model->project_level_id = $post['project_level_id'];
            $model->tag_item_id = $post['tag_item_id'];
            $model->tag_name = $post['tag_name'];
            $model->tag_description = $post['tag_description'];
            $model->tag_process_flow_id = $post['tag_process_flow_id'];
            $model->user_group_id = $post['user_group_id'];
            
            $connection = \Yii::$app->db;
            $transaction = $connection->beginTransaction();
            
            try {
                $hasError = false;
                if ($model->save()) {
                    // do something here after saving
                    
                    foreach($post['tag_assignment'] as $i => $v) {
                        $tagAssignmentModel = \backend\models\TagAssignment::findOne(['user_id' => (int) $v['user_id'], 'tag_id' => $id]);
                        
                        if(!$tagAssignmentModel) {
                            $tagAssignmentModel = new \backend\models\TagAssignment();
                            $tagAssignmentModel->user_id = (int) $v['user_id'];
                            $tagAssignmentModel->tag_id = $id;
                        }
                        
                        $tagAssignmentModel->status = 1;
                        
                        $tagAssignmentModel->process_stage_from = (int) $v['process_stage_from']['id'];
                        $tagAssignmentModel->process_stage_to = (int) $v['process_stage_to']['id'];
                        $tagAssignmentModel->mandatory = (int) $v['mandatory'];
                        $tagAssignmentModel->notification_frequency = $v['notification_frequency']['id'];
                        
                        if(!$tagAssignmentModel->save()) {
                            $hasError = true;
                        }

                        $notification_status = [];
                        
                        \backend\models\TagUserNotificationStatus::deleteAll(['tag_id' => $model->id, 'tag_assignment_id' => $tagAssignmentModel->id]);
                        
                        foreach($v['notification_status'] as $status) {
                            if($status['id']=='all') {
                                $tagAssignmentModel->notification_status = "all";
                                break;
                            }
                            else if($status['id']=='assigned') {
                                $tagAssignmentModel->notification_status = "assigned";
                                break;
                            }
                            else {
                                unset($tagNotificationStatus);
                                $tagNotificationStatus = new \backend\models\TagUserNotificationStatus();
                                $tagNotificationStatus->tag_id = $model->id;
                                $tagNotificationStatus->process_stage_id = $status['id'];
                                $tagNotificationStatus->tag_assignment_id = $tagAssignmentModel->id;

                                if(!$tagNotificationStatus->save()) {
                                    $hasError = true;
                                }
                            }
                        }
                        
                    }
                }
                else {
                    $hasError = true;
                }
                if(!$hasError) {
                    $transaction->commit();
                    return "Success";
                }
            } catch (Exception $e) {
                $transaction->rollBack();
            }
            
            $transaction->rollBack();
            return $model;
            
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
    }
    
    public function actionCreateMasterTags() {
        if (!$_POST) {
            
            $post = \yii::$app->request->post('tagDetails');
            $relatedTag = \yii::$app->request->post('relatedTags');
            
            $connection = \Yii::$app->db;
            $transaction = $connection->beginTransaction();
		
            $model = new \backend\models\Tags();            

            $model->type = "mT";
            $model->project_id = $post['project_id'];
            $model->uid = $model->generateUID(10);
            $model->project_level_id = $post['project_level_id'];
            $model->user_group_id = $post['user_group_id'];
            $model->tag_name = $post['tag_name'];
            $model->tag_description = $post['tag_description'];
                        
            $company_id = \yii::$app->user->identity->company_id;

            try {

                $hasError = false;
                
                if ($model->save()) {
                    // do something here after saving
                    
                     foreach($relatedTag as $rTag)
                    {
                        $relatedTagModel = new \backend\models\RelatedTags();
                        $relatedTagModel->tag_id = $rTag['id'];
                        $relatedTagModel->master_tag_id = $model->id;
                        $relatedTagModel->save();
                    }
                    
                    $validate[$key]['id'] = $model->id;

                    foreach($post['tag_assignment'] as $i => $v) {
                       unset($temp);
                        unset($tagAssignmentModel);
                        $temp['user_id'] = (int) $v['user_id'];
                        $temp['process_stage_from'] = (int) $v['process_stage_from']['id'];
                        $temp['process_stage_to'] = (int) $v['process_stage_to']['id'];
                        $temp['mandatory'] = (int) $v['mandatory'];
                        $notification_status = [];

                        $temp['notification_frequency'] = $v['notification_frequency']['id'];
                            
                        $tagAssignmentModel = new \backend\models\TagAssignment();

                        $tagAssignmentModel->setAttributes($temp);
                        $model->link("tagAssignment", $tagAssignmentModel);

                        foreach($v['notification_status'] as $status) {
                            if($status['id']=='all') {
                                $temp['notification_status'] = "all";
                                break;
                            }
                            else if($status['id']=='assigned') {
                                $temp['notification_status'] = "assigned";
                                break;
                            }
                            else {
                                unset($tagNotificationStatus);
                                $tagNotificationStatus = new \backend\models\TagUserNotificationStatus();
                                $tagNotificationStatus->tag_id = $model->id;
                                $tagNotificationStatus->process_stage_id = $status['id'];

                                $tagAssignmentModel->link("tagNotificationStatus", $tagNotificationStatus);
                            }
                        }
                    }
                }
                else {
                    $hasError = true;
                    \yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed.');   

                    foreach ($model->getErrors() as $attribute => $errors) {
                        $validate[$key][$attribute] = $errors;
                    }
                }
                if(!$hasError) {
                    $transaction->commit();
                    return "Success";
                }

            \yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed.');
            $transaction->rollBack();
            return $validate;
            
            } catch (Exception $e) {
                $transaction->rollBack();
            }
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
            
            if(!(isset($tag['id']) && $tag['id'] > 0))
                $models[$i]->uid = \backend\models\Tags::generateUID(10);
            
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
