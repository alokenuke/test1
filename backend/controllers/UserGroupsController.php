<?php
namespace backend\controllers;

use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
/**
 * Class TagsController
 * @package rest\versions\v1\controllers
 */
class UsergroupsController extends ApiController
{
    public $partialMatchFields;
        
    public function init() {
        $this->modelClass = 'backend\models\UserGroups';
        
        $this->partialMatchFields = ['group_name'];
        
        parent::init();
    }
    
    public function actionSearch() {
        if (!$_POST) {
            
            $_GET['expand'] = 'levels, projectIds';
            
            $post = \Yii::$app->request->post();
            
            $model = new $this->modelClass;
            
            $query = $model->find();
            
            if(isset($post['select']['UserGroups']))
               $query->select($post['select']['UserGroups']);

            if(isset($post['search'])) {
                foreach($post['search'] as $key => $val)
                    if(isset($val)) {
                        if(in_array($key, $this->partialMatchFields))
                            $query->andWhere(['like', $key, $val]);
                        else
                            $query->where([$key => $val]);
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
    
    public function actionAssignprojects($id) {
        if (!$_POST) {
            
            $post = \Yii::$app->request->post();
            
            $result = [];
            
            if(isset($post['Projects'])) {
                $companyId = \yii::$app->user->identity->company_id;
                foreach($post['Projects'] as $project) {
                    $model = new \backend\models\UserGroupProjects();
                    $model->project_id = $project;
                    $model->user_group_id = $id;
                    $model->assigned_by = \yii::$app->user->identity->id;
                    $result[] = $model->save();
                }
            }
            else {
                throw new \yii\web\HttpException(404, 'Invalid Request');
            }
            
            return $result;
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
    }
    
    public function actionUnassignprojects($id) {
        if (!$_POST) {
            
            $post = \Yii::$app->request->post();
            
            $model = new \backend\models\UserGroupProjects();
            
            $result = 0;
            
            if(isset($post['Projects'])) {
                $result = $model->deleteAll("user_group_id = :group_id and project_id in (:project_id)", ['group_id' => $id, 'project_id' => implode(",", $post['Projects'])]);
            }
            else {
                throw new \yii\web\HttpException(404, 'Invalid Request');
            }
            
            return $result;
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
    }
    
    public function actionGetall() {
        return parent::actionGetall();
    }
}
