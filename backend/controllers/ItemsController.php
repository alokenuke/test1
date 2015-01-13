<?php
namespace backend\controllers;

use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
/**
 * Class TagsController
 * @package rest\versions\v1\controllers
 */
class ItemsController extends ApiController
{
    public $partialMatchFields;
        
    public function init() {
        $this->modelClass = 'backend\models\Items';
        
        $this->partialMatchFields = ['item_name'];
        
        parent::init();
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
    
    public function actionAssignprojects($id) {
        if (!$_POST) {
            
            $post = \Yii::$app->request->post();
            
            $result = [];
            
            if(isset($post['Projects'])) {
                foreach($post['Projects'] as $project) {
                    $model = new \backend\models\ItemsProjects();
                    $model->project_id = $project;
                    $model->item_id = $id;
                    $model->created_by = \yii::$app->user->identity->id;
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
            
            $model = new \backend\models\ItemsProjects();
            
            $result = 0;
            
            if(isset($post['Projects'])) {
                $result = $model->deleteAll("item_id = :item_id and project_id in (:project_id)", ['item_id' => $id, 'project_id' => implode(",", $post['Projects'])]);
            }
            else {
                throw new \yii\web\HttpException(404, 'Invalid Request');
            }
            
            return $result;
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
    }
    
    public function actionGetItems() {
        $result = static::getMenuRecrusive(0);
        return $result;
    }
    
    public function actionGetall() {
        return parent::actionGetall();
    }
    
}
