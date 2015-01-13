<?php
namespace backend\controllers;

use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
/**
 * Class TagsController
 * @package rest\versions\v1\controllers
 */
class UserlevelsController extends ApiController
{
    public $partialMatchFields;
        
    public function init() {
        $this->modelClass = 'backend\models\UserLevels';
        
        $this->partialMatchFields = ['level_name'];
        
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
    
    public function actionAssignusers($id) {
        if (!$_POST) {
            
            $post = \Yii::$app->request->post();
            
            $result = [];
            
            if(isset($post['User'])) {
                $companyId = \yii::$app->user->identity->company_id;
                foreach($post['User'] as $user) {
                    $model = new \backend\models\RelUserLevelsUsers();
                    $model->user_id = $user;
                    $model->company_id = $companyId;
                    $model->user_level_id = $id;
                    $model->user_group_id = $post['group_id'];
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
    
    public function actionUnassignusers($id) {
        if (!$_POST) {
            
            $post = \Yii::$app->request->post();
            
            $model = new \backend\models\RelUserLevelsUsers();
            
            $result = 0;
            
            if(isset($post['User'])) {
                $result = $model->deleteAll("company_id = :company_id and user_level_id = :level_id and user_id in (:user_id)", ['company_id' => \yii::$app->user->identity->company_id, 'level_id' => $id, 'user_id' => implode(",", $post['User'])]);
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
        $_GET['expand'] = "relateUsers";
        return parent::actionGetall();
    }
    
}
