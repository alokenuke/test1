<?php
namespace backend\controllers;

use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
/**
 * Class TagsController
 * @package rest\versions\v1\controllers
 */
class TimeAttendanceLogController extends ApiController
{
    public $partialMatchFields;
        
    public function init() {
        $this->modelClass = 'backend\models\TimeattendanceLog';
        
        $this->partialMatchFields = ['tag_name', 'tag_description'];
        
        parent::init();
    }
    
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
        $behaviors['access'] = [
                'class' => \backend\models\RoleAccess::className(),
                'rules' => [
                    [
                        'actions' => ['search', 'getall', 'index', 'create', 'update', 'view', 'delete'],
                        'allow' => true,
                        'roles' => ['Client'],
                    ],
                ]
        ];
        
        return $behaviors;
    }
    
    public function actionSearch() {
        if (!$_POST) {
            
            $post = \Yii::$app->request->post();
            
            $model = new $this->modelClass;
            
            $query = $model->find();
            
            if(isset($post['select']))
               $query->select($select);
            
            $query->joinWith("timeattendance");

            if(isset($post['search'])) {
                foreach($post['search'] as $key => $val)
                    if(isset($val)) {
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
                        else if($key=='usergroup') {
                            $query->andWhere(['user_group_id' => $val['id']]);
                        }
                        else if($key=="employee_name") {
                            $query->joinWith("user");
                            $query->andWhere("user.first_name LIKE :name OR user.last_name LIKE :name", ['name' => "%$val%"]);
                        }
                        else if(in_array($key, $this->partialMatchFields))
                            $query->andWhere(['like', $key, $val]);
                        else
                            $query->andWhere([$key => $val]);
                    }
            }
            
            if(isset($post['sort']))
                $_GET['sort'] = $post['sort'];
            if(isset($post['page']))
                $_GET['page'] = $post['page'];
            
            try {
                $provider = new ActiveDataProvider ([
                    'query' => $query
                ]);
            } catch (Exception $ex) {
                throw new \yii\web\HttpException(500, 'Internal server error');
            }
            return $provider;
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
    }
}
