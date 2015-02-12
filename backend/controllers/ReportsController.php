<?php
namespace backend\controllers;

use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
/**
 * Class TagsController
 * @package rest\versions\v1\controllers
 */
class ReportsController extends ApiController
{
    public $partialMatchFields;
        
    public function init() {
        $this->modelClass = 'backend\models\Tags';
        
        $this->partialMatchFields = ['tag_name', 'tag_description', 'uid', 'product_code'];
        
        parent::init();
    }
    
    // Get list of tags for label printing
    public function actionLabels() {
        if (!$_POST) {
            
            $_GET['expand'] = "project_level, itemObj, userGroup,processObj";
            
            $post = \Yii::$app->request->post();
            
            $model = new $this->modelClass;
            
            $query = $model->find();
            
            if(isset($post['search'])) {
                foreach($post['search'] as $key => $val) {
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
                            $query->andWhere([$key => $val]);
                    }
                }
            }
            
            $pageLimit = 20;
            
            try {
                $provider = new ActiveDataProvider ([
                    'query' => $query,
                    'pagination'=> false,
                ]);
            } catch (Exception $ex) {
                throw new \yii\web\HttpException(500, 'Internal server error');
            }
            return $provider;
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
    }
    
    public function actionSearch() {
        if (!$_POST) {
            
            $post = \Yii::$app->request->post();
            
            $model = new $this->modelClass;
            
            $query = $model->find();
            
            if(isset($post['select']))
               $query->select($post['select']);

            if(isset($post['search'])) {
                foreach($post['search'] as $key => $val)
                    if(isset($val)) {
                        if(in_array($key, $this->partialMatchFields))
                            $query->andWhere(['like', $key, $val]);
                        else
                            $query->where([$key => $val]);
                    }
            }
                       
            if(isset($post['excludeProjects'])) {
                $projectIds = [];
                foreach($post['excludeProjects'] as $project)
                    $projectIds[] = $project['id'];
                
                $query->andWhere(['not in', 'id', $projectIds]);
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
}
