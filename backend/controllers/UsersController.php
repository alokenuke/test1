<?php
namespace backend\controllers;

use yii;
use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
use yii\base\Model;
use yii\bootstrap\ActiveForm;
/**
 * Class TagsController
 * @package rest\versions\v1\controllers
 */
class UsersController extends ApiController
{
    public $partialMatchFields;
        
    public function init() {
        $this->modelClass = 'backend\models\User';
        
        $this->partialMatchFields = ['name','username','email','contact_number'];
        
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
                    if($key=="name") {
                            $query->orwhere(['like', 'first_name', $val]);
                            $query->orwhere(['like', 'last_name', $val]);
                    } else if( $key == "usergroups"){
                            $query->leftJoin('rel_user_levels_users rel_ul', 'rel_ul.user_id=user.id')->andWhere(["rel_ul.user_group_id" => $val['id']]);
                    }else if(is_array ($val)) {
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
    
    public function actionLevelusers($id) {
        if (!$_POST) {
            
            $post = \Yii::$app->request->post();
            
            $model = new $this->modelClass;
            
            $query = $model->find()
                    ->leftJoin('rel_user_levels_users rel_ul', 'rel_ul.user_id=user.id')->andWhere(["rel_ul.user_level_id" => $id]);
            
            if(isset($post['search'])) {
                foreach($post['search'] as $key => $val)
                    if($key=="name") {
                            $query->orwhere(['like', 'first_name', $val]);
                            $query->orwhere(['like', 'last_name', $val]);
                    } else if(is_array ($val)) {
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
    
    function actionMultiinsert(){
        
        $post = Yii::$app->request->post();
        
        $models = array_fill ( 0 , count($post) , new $this->modelClass );
        
        Model::loadMultiple($models, $post);
        
        $validate = $this->validateMultiple($models);
                
        if (!count($validate)) {
            $count = 0;
            foreach ($models as $item) {
               // populate and save records for each model
                if ($item->save()) {
                    // do something here after saving
                    $count++;
                }
            }
            return "Success";
        }
        
        Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed.');               
        
        return $validate;
    }
    
    public static function validateMultiple($models, $attributes = null)
    {
        $result = [];
        /* @var $model Model */
        foreach ($models as $i => $model) {
            $model->validate($attributes);
            foreach ($model->getErrors() as $attribute => $errors) {
                $result['User'][$i][$attribute] = $errors;
            }
        }
        
        return $result;
    }
    
}
