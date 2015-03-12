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
    
    public function actionGetstages() {
        if (!$_POST) {
            try {
                
                $uid = \Yii::$app->request->post("uid");
                
                $tagModel = \backend\models\Tags::findOne(['uid' => $uid]);
                
                if(!$tagModel) {
                    $model = new \backend\models\Tags();
                    $model->addError("uid", "Invalid UID.");
                    return $model;
                }
                
                $tagDetails = $tagModel->toArray([], ["tagActivityLog"]);
                
                $tagProcessFlow = \backend\models\TagProcess::findOne(['id' => $tagModel->tag_process_flow_id]);
                
                $params = (array) json_decode($tagProcessFlow->params);
                
                $_GET['expand'] = "childOptions";
                
                $admin = false;
                
                if(!$params['flagHierarchy'] || $admin) {
                    $query = \backend\models\TagProcess::find()->andWhere(['parent_id' => $tagProcessFlow->id])->orderBy("position");
                    
                    $provider = new ActiveDataProvider ([
                        'query' => $query,
                        'pagination'=> false,
                    ]);
                    return $provider;
                }
                else {
                    $tagAssignment = \backend\models\TagAssignment::find()->andWhere(['tag_id' => $tagDetails['id'], 'user_id' => \yii::$app->user->id])->one();
                    
                    if($tagAssignment) {
                        
                        $lastActivity = $tagDetails['tagActivityLog'];
                    
                        $assignedProcessDetails = \backend\models\TagProcess::find()
                                ->select(['position'])
                                ->andWhere(["id" => [$tagAssignment->process_stage_from, $tagAssignment->process_stage_to]])
                                ->orderBy("position")
                                ->all();
                        
                        $processStart = $assignedProcessDetails[0]->position;
                        $processEnd = $assignedProcessDetails[1]->position;
                        
                        if(!isset($lastActivity) || !isset($lastActivity['stageInfo'])) {
                            return \backend\models\TagProcess::find()->andWhere(['parent_id' => $tagProcessFlow->id])
                                ->andWhere(['between', "position", $processStart, $processEnd])
                                ->orderBy("position")->one();
                        }
                        if(($lastActivity['stageInfo']['option_type']==3 && $lastActivity['answer']==100) || ($lastActivity['stageInfo']['option_type']==5 && $lastActivity['answer']!="") || ($lastActivity['stageInfo']['option_type']==1 && array_search($lastActivity['stageInfo']['flagCompletion'], explode(",", $lastActivity['answer']))) || (isset($lastActivity['answer']['id']) && $lastActivity['stageInfo']['flagCompletion']==$lastActivity['answer']['id'])) {
                            return \backend\models\TagProcess::find()->andWhere(['parent_id' => $tagProcessFlow->id])
                                ->andWhere(['between', "position", $processStart, $processEnd])
                                ->andWhere(['>', "position", $lastActivity['stageInfo']['position']])
                                ->andWhere(["<>", 'id', $lastActivity['stageInfo']['id']])
                                ->orderBy("position")->one();
                        }
                        else {
                            return \backend\models\TagProcess::find()->andWhere(['parent_id' => $tagProcessFlow->id])
                                ->andWhere(['between', "position", $processStart, $processEnd])
                                ->andWhere(['id' => $lastActivity['stageInfo']['id']])
                                ->orderBy("position")->one();
                        }
                    }
                    else
                        return null;
                }

            } catch(Exception $ex) {
                throw new \yii\web\HttpException(404, 'Invalid Request');
            }
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
    }
    
    public function actionSearch() {
        if (!$_POST) {
            
            if(!isset($_GET['expand']))
                $_GET['expand'] = "project_level, itemDetails, userGroup";
            else
                $_GET['expand'] = "project_level, itemDetails, userGroup,".$_GET['expand'];
            $post = \Yii::$app->request->post();
            
//            $model = new $this->modelClass;
            $model = new \backend\models\Tags();
            
            $query = $model->find();
            
            if(isset($post['select']))
               $query->select($post['select']);

            if(isset($post['search'])) {
                foreach($post['search'] as $key => $val)
                    if($key=="globalSearch") {
                        $query->andWhere("tag_name like :search or tag_description like :search or uid like :search or product_code like :search", ['search' => "%$val%" ]);
                    }
                    else if($key=="date_range") {
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
                            $query->andWhere(['project_id' => $val['project']['id']]);
                    }
                    else if($key == 'tag_process_flow_id') {
                        $processDetails = \backend\models\TagProcess::findOne(['id' => $val]);
                        
                        $processParams = (array) json_decode($processDetails->params);
                        
                        if($processDetails->type==1) {
                            $query->andWhere([$key => $val]);
                        }
                        else if($processDetails->type==2) {
                            $query->andWhere([$key => $processDetails->parent_id]);
                            
                            if($processDetails->position==0) {
                            
                                if($processDetails->option_type==1)
                                {
                                    $query->andWhere("(tag_activity_log.process_stage_id = $processDetails->id and :answer not in (tag_activity_log.process_stage_answer) OR (select count(log.id) from tag_activity_log log where log.tag_id=tags.id) = 0)", [':answer' => $processParams['flagCompletion']]);
                                }
                                else if($processDetails->option_type==3)
                                {
                                    $query->andWhere("(tag_activity_log.process_stage_id = $processDetails->id and tag_activity_log.process_stage_id = :process_stage) and tag_activity_log.process_stage_answer <> :answer", [':process_stage' => $processDetails->id, ':answer' => "100"]);
                                }
                                else if($processDetails->option_type==5)
                                {
                                    $query->andWhere("(tag_activity_log.process_stage_id = $processDetails->id and tag_activity_log.process_stage_id = :process_stage) and tag_activity_log.process_stage_answer = :answer", [':process_stage' => $processDetails->id, ':answer' => ""]);
                                }
                                else
                                {
                                    $query->andWhere("(tag_activity_log.process_stage_id = $processDetails->id and tag_activity_log.process_stage_answer <> :answer OR (select count(log.id) from tag_activity_log log where log.tag_id=tags.id) = 0)", [':answer' => $processParams['flagCompletion']]);
                                }

                                $query->join("left join", "tag_activity_log", "tags.id = tag_activity_log.tag_id and tag_activity_log.status = 1")->groupBy("tags.id");
                                
                                $query->having("count(tag_activity_log.id) = 0 OR (count(tag_activity_log.id) > 0 and max(tag_activity_log.logged_date))");
                            }
                            else {
                                
                                $lastProcess = \backend\models\TagProcess::find()->andWhere(['parent_id' => $processDetails->parent_id])->andWhere(['<', 'position', $processDetails->position])->one();
                                $lastProcessParams = (array) json_decode($lastProcess->params);
                                
                                $subQryCheckCurrentProcess = "";
                                $subQryCheckLastProcess = "";
                                
                                if($processDetails->option_type == 1) 
                                    $subQryCheckCurrentProcess = "(tag_activity_log.process_stage_id = $processDetails->id and ($processParams[flagCompletion]) NOT IN (tag_activity_log.process_stage_answer))";
                                else if($processDetails->option_type==3)
                                    $subQryCheckCurrentProcess = "(tag_activity_log.process_stage_id = $processDetails->id and tag_activity_log.process_stage_answer <> 100)";
                                else if($processDetails->option_type==5)
                                    $subQryCheckCurrentProcess = "(tag_activity_log.process_stage_id = $processDetails->id and tag_activity_log.process_stage_answer = '')";
                                else
                                    $subQryCheckCurrentProcess = "(tag_activity_log.process_stage_id = $processDetails->id and tag_activity_log.process_stage_answer <> $processParams[flagCompletion])";
                                
                  
                                if($lastProcess->option_type == 1)
                                    $subQryCheckLastProcess = "(tag_activity_log.process_stage_id = $lastProcess->id and ($lastProcessParams[flagCompletion]) IN (tag_activity_log.process_stage_answer))";
                                else if($lastProcess->option_type==3)
                                    $subQryCheckLastProcess = "(tag_activity_log.process_stage_id = $lastProcess->id and tag_activity_log.process_stage_answer = 100)";
                                else if($lastProcess->option_type==5)
                                    $subQryCheckLastProcess = "(tag_activity_log.process_stage_id = $lastProcess->id and tag_activity_log.process_stage_answer <> '')";
                                else if($lastProcess->option_type != 3 && $lastProcess->option_type != 5) 
                                    $subQryCheckLastProcess = "(tag_activity_log.process_stage_id= $lastProcess->id and tag_activity_log.process_stage_answer = $lastProcessParams[flagCompletion])";
                                
                                $query->andWhere("$subQryCheckCurrentProcess OR $subQryCheckLastProcess");

                                $query->join("left join", "tag_activity_log", "tags.id = tag_activity_log.tag_id and tag_activity_log.status = 1")->groupBy("tags.id");
                                
                                $query->having("count(tag_activity_log.id) > 0 and max(tag_activity_log.logged_date)");
                            }
                        }
                        else if($processDetails->type==3) {
                            $processStage = $processDetails->getParentProcess()->one();
                            $query->andWhere([$key => $processStage->parent_id]);
                            
                            if($processDetails->option_type!=3 && $processDetails->option_type!=5)
                            {
                                $query->andWhere("(tag_activity_log.process_stage_id = :process_stage) and tag_activity_log.process_stage_answer = :answer", [':process_stage' => $processStage->id, ':answer' => $processDetails->id]);
                            }
                            
                            $query->join("left join", "tag_activity_log", "tags.id = tag_activity_log.tag_id and tag_activity_log.status = 1")->groupBy("tags.id");
                            
                            if($processDetails->position==0) {
                                $query->having("max(tag_activity_log.logged_date)");
                            }
                        }
                    }
                    else if(isset($val)) {
                        if(in_array($key, $this->partialMatchFields))
                            $query->andWhere(['like', $key, $val]);
                        else
                            $query->andWhere([$key => $val]);
                    }
            }

            if(isset($post['excludeTags'])) {
                $tagIds = [];
                foreach($post['excludeTags'] as $tag)
                    $tagIds[] = $tag['id'];
                
                $query->andWhere(['not in', 'id', $tagIds]);
            }
                        $pageLimit = 20;
            if(isset($post['sort']) && $post['sort'])
                $_GET['sort'] = $post['sort'];
            else
                $_GET['sort'] = "-created_date";
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
    
    public function actionGetTag() {
        if (!$_POST) {
            
            if(!isset($_GET['expand']))
                $_GET['expand'] = "project_level, processDetails, itemDetails, userGroup";
            else
                $_GET['expand'] = "project_level, processDetails, itemDetails, userGroup,".$_GET['expand'];
            
            $uid = \Yii::$app->request->post("uid");
            
            $model = new $this->modelClass;
            
            $query = $model->find()->andWhere(['uid' => $uid])->one();
            
            if($query && $query->uid == $uid)
                return $query;
            else
            {
                $model->addError("uid", "Invalid UID.");
                return $model;
            }
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
                            if(!isset($v['notification_frequency']) && !(isset($v['process_stage_from']) || isset($v['notification_status'])))
                                continue;
                            
                            unset($temp);
                            unset($tagAssignmentModel);
                            $temp['user_id'] = (int) $v['user_id'];
                            $temp['process_stage_from'] = (int) $v['process_stage_from']['id'];
                            $temp['process_stage_to'] = (int) $v['process_stage_to']['id'];
                            $temp['mandatory'] = (int) $v['mandatory'];
                            $notification_status = [];
                            
                            $temp['notification_frequency'] = $v['notification_frequency']['id'];

                            $tagAssignmentModel = new \backend\models\TagAssignment();
                            
                            if($this->in_array_r('id', 'all', $v['notification_status'])) {
                                $temp['notification_status'] = "all";
                            }
                            else if($this->in_array_r('id', 'assigned', $v['notification_status'])) {
                                $temp['notification_status'] = "assigned";
                            }
                            else
                                $tagAssignmentModel->notification_status = "";
                            
                            $tagAssignmentModel->setAttributes($temp);

                            $tag->link("tagAssignment", $tagAssignmentModel);
                            
                            if(!$tagAssignmentModel->notification_status) {
                                foreach($v['notification_status'] as $status) {
                                    if(!in_array($status['id'], ['all', 'assigned'])) {
                                        unset($tagNotificationStatus);
                                        $tagNotificationStatus = new \backend\models\TagUserNotificationStatus();
                                        $tagNotificationStatus->tag_id = $tag->id;
                                        $tagNotificationStatus->process_stage_id = $status['id'];

                                        $tagAssignmentModel->link("tagNotificationStatus", $tagNotificationStatus);
                                    }
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
                        
                        if(isset($v['process_stage_from']['id']))
                            $tagAssignmentModel->process_stage_from = (int) $v['process_stage_from']['id'];
                        
                        if(isset($v['process_stage_to']['id']))
                            $tagAssignmentModel->process_stage_to = (int) $v['process_stage_to']['id'];
                        
                        if(isset($v['mandatory']))
                            $tagAssignmentModel->mandatory = (int) $v['mandatory'];
                        else
                            $tagAssignmentModel->mandatory = 0;
                        if(isset($v['notification_frequency']))
                            $tagAssignmentModel->notification_frequency = $v['notification_frequency']['id'];
                        
                        if(isset($v['notification_status']) && $this->in_array_r('id', 'all', $v['notification_status'])) {
                            $tagAssignmentModel->notification_status = "all";
                        }
                        else if(isset($v['notification_status']) && $this->in_array_r('id', 'assigned', $v['notification_status'])) {
                            $tagAssignmentModel->notification_status = "assigned";
                        }
                        else
                            $tagAssignmentModel->notification_status = "";
                        
                        if(!$tagAssignmentModel->save()) {
                            $hasError = true;
                            return $tagAssignmentModel;
                        }

                        $notification_status = [];
                        
                        \backend\models\TagUserNotificationStatus::deleteAll(['tag_id' => $model->id, 'tag_assignment_id' => $tagAssignmentModel->id]);
                        
                        if(isset($v['notification_status']) && !$tagAssignmentModel->notification_status) {
                            foreach($v['notification_status'] as $status) {
                                if(!in_array($status['id'], ['all', 'assigned'])) {
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
            $model->tag_item_id = $post['tag_item_id'];
            $model->tag_process_flow_id = $post['tag_process_flow_id'];
                        
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
                        
                        if($this->in_array_r('id', 'all', $v['notification_status'])) {
                            $temp['notification_status'] = "all";
                        }
                        else if($this->in_array_r('id', 'assigned', $v['notification_status'])) {
                            $temp['notification_status'] = "assigned";
                        }
                        else
                            $tagAssignmentModel->notification_status = "";

                        $tagAssignmentModel->setAttributes($temp);
                        $model->link("tagAssignment", $tagAssignmentModel);

                        if(!$tagAssignmentModel->notification_status) {
                            foreach($v['notification_status'] as $status) {
                                if(!in_array($status['id'], ['all', 'assigned'])) {
                                    unset($tagNotificationStatus);
                                    $tagNotificationStatus = new \backend\models\TagUserNotificationStatus();
                                    $tagNotificationStatus->tag_id = $model->id;
                                    $tagNotificationStatus->process_stage_id = $status['id'];

                                    $tagAssignmentModel->link("tagNotificationStatus", $tagNotificationStatus);
                                }
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
    
    public function actionUpdatemastertags($id) {

     if (!$_POST) {

        $post = \yii::$app->request->post('tagDetails');
        $relatedTag = $post['relatedTags'];

            $model = $model = \backend\models\Tags::findOne(['id' => $id]);
            $connection = \Yii::$app->db;
            $transaction = $connection->beginTransaction();
            $model->type = "mT";
            $model->project_id = $post['project_id'];
            $model->uid = $model->generateUID(10);
            $model->project_level_id = $post['project_level_id'];
            $model->user_group_id = $post['user_group_id'];
            $model->tag_name = $post['tag_name'];
            $model->tag_description = $post['tag_description'];
            $model->tag_item_id = $post['tag_item_id'];
            $model->tag_process_flow_id = $post['tag_process_flow_id'];

            $company_id = \yii::$app->user->identity->company_id;

        try {

            $hasError = false;

            if ($model->save()) { 
                \backend\models\RelatedTags::deleteAll(['master_tag_id' => $model->id]);
                foreach($relatedTag as $rTag)
                {
                    $relatedTagModel = new \backend\models\RelatedTags();
                    $relatedTagModel->tag_id = $rTag['id'];
                    $relatedTagModel->master_tag_id = $model->id;
                    $relatedTagModel->save();
                }

                $validate[$key]['id'] = $model->id;

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
                        
                        if($this->in_array_r('id', 'all', $v['notification_status'])) {
                            $tagAssignmentModel->notification_status = "all";
                        }
                        else if($this->in_array_r('id', 'assigned', $v['notification_status'])) {
                            $tagAssignmentModel->notification_status = "assigned";
                        }
                        else
                            $tagAssignmentModel->notification_status = "";
                        
                        if(!$tagAssignmentModel->save()) {
                            $hasError = true;
                        }

                        $notification_status = [];
                        
                        \backend\models\TagUserNotificationStatus::deleteAll(['tag_id' => $model->id, 'tag_assignment_id' => $tagAssignmentModel->id]);
                        
                        if(!$tagAssignmentModel->notification_status) {
                            foreach($v['notification_status'] as $status) {
                                if(!in_array($status['id'], ['all', 'assigned'])) {
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
            }
            else {
                $hasError = true;
                \yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed.');   

                foreach ($model->getErrors() as $attribute => $errors) {
                    $validate[$attribute] = $errors;
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
            $models[$i]->tag_name = $tag['pre']."-".$tag['tagName']."-".$tag['post'];
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
