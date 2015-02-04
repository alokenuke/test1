<?php
namespace backend\controllers;

use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
/**
 * Class TagsController
 * @package rest\versions\v1\controllers
 */
class ProjectLevelController extends ApiController
{
    public $partialMatchFields;
        
    public function init() {
        $this->modelClass = 'backend\models\ProjectLevel';
        
        $this->partialMatchFields = ['project_name', 'address', 'location', 'city'];
        
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
                    $model = new \backend\models\ProjectLevelProjects();
                    $model->project_id = $project;
                    $model->level_id = $id;
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
            
            $model = new \backend\models\ProjectLevelProjects();
            
            $result = 0;
            
            if(isset($post['Projects'])) {
                $result = $model->deleteAll("level_id = :level_id and project_id in (:project_id)", ['level_id' => $id, 'project_id' => implode(",", $post['Projects'])]);
            }
            else {
                throw new \yii\web\HttpException(404, 'Invalid Request');
            }
            
            return $result;
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
    }
    
    public function actionSavelevelpositions() {
        if (!$_POST) {
            
            $post = \Yii::$app->request->post("ProjectLevels");
            
            $result = [];
            
            if(isset($post)) {
                $this->saveChildLevels($post);
            }
            else {
                throw new \yii\web\HttpException(404, 'Invalid Request');
            }
            
            return $result;
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
    }
    
    public function saveChildLevels($levels, $parent=0) {
        $result = [];
        if(count($levels)>0) {
            foreach($levels as $key => $level) {
                if($model = \backend\models\ProjectLevel::findOne(['id' => $level['id'], ['>', 'parent_id', 0]])) {
                    $model->position = $key;
                    $model->parent_id = $parent;
                    $result[] = $model->save();
                }
                $this->saveChildLevels($level['levels'], $level['id']);
            }
        }
        return $result;
    }
    
    public function actionGetall() {
        return parent::actionGetall();
    }
    
}
