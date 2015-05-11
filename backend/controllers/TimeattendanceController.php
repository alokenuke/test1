<?php
namespace backend\controllers;

use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
/**
 * Class TagsController
 * @package rest\versions\v1\controllers
 */
class TimeattendanceController extends ApiController
{
    public $partialMatchFields;
        
    public function init() {
        $this->modelClass = 'backend\models\Timeattendance';
        
        $this->partialMatchFields = ['tag_name', 'tag_description', 'uid', 'product_code'];
        
        parent::init();
    }
    
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
        $behaviors['access'] = [
                'class' => \backend\models\RoleAccess::className(),
                'rules' => [
                    [
                        'actions' => ['search', 'getall', 'scan', 'save', 'index', 'create', 'update', 'view', 'delete'],
                        'allow' => true,
                        'roles' => ['Client'],
                    ],
                ]
        ];
        
        return $behaviors;
    }
    
    public function actionScan() {
        if (!$_POST) {
            
            $params = \Yii::$app->request->post();
            
            $uid = $params["uid"];
            
            $model = new $this->modelClass;
            
            $model = $model->find()->andWhere(['uid' => $uid])->one();
            
            if($model) {
                $assignment = \backend\models\TimeattendanceAssignment::findOne(['tag_id' => $model->id, 'user_id' => \yii::$app->user->identity->id]);
                if($assignment) {
                    $loggedIn = \backend\models\TimeattendanceLog::find()->andWhere(['tag_id' => $model->id, 'logged_by' => \yii::$app->user->identity->id])->andWhere(['>', 'login_time', date("Y-m-d H:i:s", strtotime("-12 hours"))])->andWhere(["logout_time" => NULL])->andWhere(["status" => 1])->one();

                    if($loggedIn) {
                        $loggedIn->logout_time = date("Y-m-d H:i:s");
                        if($loggedIn->save())
                            return "Successfully logged out.";
                        else
                            return $loggedIn;
                    }
                    else
                    {
                        $timeAttendanceLog = new \backend\models\TimeattendanceLog();
                        $timeAttendanceLog->tag_id = $model->id;
                        $timeAttendanceLog->location = json_encode(["lat" => $params["location"]["lat"], "long" => $params["location"]["long"]]);
                        $timeAttendanceLog->device = $params["device"];
                        $timeAttendanceLog->logged_by = \yii::$app->user->identity->id;
                        $timeAttendanceLog->login_time = date("Y-m-d H:i:s");
                        $timeAttendanceLog->status = 1;
                        if($timeAttendanceLog->save())
                            return "Successfully logged in.";
                        else
                            return $timeAttendanceLog;
                    }
                }
                else
                {
                    $model->addError("uid", "This tag is not assigned to you.");
                    return $model;
                }
            }
            else
            {
                $model = new $this->modelClass;
                $model->addError("uid", "Invalid UID.");
                return $model;
            }
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
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
                            $val['to_date'] = date("Y-m-d", strtotime($val['to_date'])+86399);
                            $query->andWhere(['between', 'created_date', $val['from_date'], $val['to_date']]);
                        }
                        else if(isset($val['from_date'])) {
                            $val['from_date'] = date("Y-m-d", strtotime($val['from_date']));
                            $query->andWhere(['>=', 'created_date', $val['from_date']]);
                        }
                        else if(isset($val['to_date'])) {
                            $val['to_date'] = date("Y-m-d", strtotime($val['to_date'])+86399);
                            $query->andWhere(['<=', 'created_date', $val['to_date']]);
                        }
                    }
                    else if(is_array ($val)) {
                        if(isset($val['project']))
                            $query->andWhere(['project_id' => $val['project']['id']]);
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
    
    public function actionSave() {
        if (!$_POST) {
            
            $post = \yii::$app->request->post("tagDetails");
            
            
            $connection = \Yii::$app->db;
            $transaction = $connection->beginTransaction();
            
            $model = new \backend\models\Timeattendance();
            if(isset($post['tag']['id']) && $post['tag']['id']) {
                $model = $model->findOne($post['tag']['id']);
            }
            
            $model->setAttribute("tag_name", $post['tag']['tag_name']);
            $model->setAttribute("tag_description", $post['tag']['tag_description']);
            if(!$model->id)
                $model->setAttribute('uid', $model->generateUID(10));
            $model->setAttribute('project_id', $post['project_id']);
            $model->setAttribute('project_level_id', $post['project_level_id']);
            $model->setAttribute('user_group_id', $post['user_group_id']);
            
            $company_id = \yii::$app->user->identity->company_id;

            try {

                if ($model->save()) {
                    // do something here after saving

                    foreach($post['tag_assignment'] as $i => $v) {
                        unset($temp);
                        unset($timeattendanceAssignmentModel);
                        $temp['user_id'] = (int) $v['user_id'];
                        
                        $timeattendanceAssignmentModel = new \backend\models\TimeattendanceAssignment();

                        $timeattendanceAssignmentModel->setAttributes($temp);

                        $model->link("timeattendanceLog", $timeattendanceAssignmentModel);
                        
                        if($timeattendanceAssignmentModel->hasErrors()) {
                            $transaction->rollBack();
                            return $timeattendanceAssignmentModel;
                        }
                    }
                    $transaction->commit();
                    return "Success";
                }
                else {
                    $transaction->rollBack();
                    return $model;
                }
            } catch (Exception $e) {
                $transaction->rollBack();
            }
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
    }
    
}
