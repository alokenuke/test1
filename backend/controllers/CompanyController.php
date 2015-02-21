<?php
namespace backend\controllers;

use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;

/**
 * Class TagsController
 * @package rest\versions\v1\controllers
 */
class CompanyController extends ApiController
{
    public $partialMatchFields;
        
    public function init() {
        $this->modelClass = 'backend\models\Company';
        
        $this->partialMatchFields = ['company_name', 'company_owner'];
                
        parent::init();
    }
    
    public function actionDefault() {
        if (!$_POST) {
            
            $post = \Yii::$app->request->post();
            
            $model = new $this->modelClass;

            $query = $model->find(['id' => \yii::$app->user->identity->company_id]);
            
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
            
            return $query->one();
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
    
    public function actionStats() {
        if (!$_POST) {
            
            $company = new \backend\models\Company();
            
            $return = array();
            $return['projects']['count'] = \backend\models\Projects::find()->where(['project_status' => 1])->count();
            $return['tags']['count'] = \backend\models\Tags::find()->where(['tag_status' => 1])->count();
            $return['users']['count'] = \backend\models\User::find()->where(['status' => 1])->andWhere(['user.status' => 1])->count();
            $return['items']['count'] = \backend\models\Items::find()->where(['status' => 1])->count();
            
            $return['data']['count'] = $company->getFolderSize(\Yii::$app->params['repository']);
            
            return $return;
            
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
    }
    
    public function actionGetall() {
        if(!isset($post['sort']))
            $_GET['sort'] = "company_name";
        
        return parent::actionGetall();
    }
    
    
    public function actionSavecompany() {
        
        $data = \Yii::$app->request->post();
        
        $connection = \Yii::$app->db;
        $transaction = $connection->beginTransaction();        
       
        if(isset($data['company']['id']) && $data['company']['id']>0)
                $company = \backend\models\Company::findOne($data['company']['id']);
            else
                 $company = new \backend\models\Company();
        
        $company->setAttributes($data['company']);
        
        if($company->company_logo) {
            if(isset($company->company_logo) && strpos($company->company_logo,'temp') !== false) {
                $company->temp_path = $company->company_logo;
                $company->company_logo = array_pop(explode('/',$company->company_logo));
            }
            else
                $company->company_logo = array_pop(explode('=',$company->company_logo));
        }
        
        if(strtotime($company->expiry_date))
            $company->expiry_date = date("Y-m-d", strtotime($company->expiry_date));
        else
            $company->addError ("expiry_date", "Invalid expiry date.");
        
        if($company->save()){
           if (isset($data['company']['id']) && $data['company']['id'] > 0) {
                $role = \backend\models\Roles::findOne(['company_id' => $data['company']['id']]);
            } else { 
                $role = new \backend\models\Roles();
                $role->role_name = 'Super Admin';
                $role->type = 'Client';
                $role->company_id = $company->id;
                $role->status = 1;
                $role->created_date = date('Y-m-d');
                $role->save();
            }
            
            if(isset($data['user']['id']) && $data['user']['id']>0)
                $user = \backend\models\User::findOne($data['user']['id']);
            else {
                $user = new \backend\models\User();
                $user->role = $role->id;
            }
            
            $user->setAttributes($data['user']);
            $user->company_id = $company->id;
            if(!$user->save()) {
                $transaction->rollBack();
                return $user;
            }
        } else {
            $transaction->rollBack();
            return $company;
        }
        
        $transaction->commit();
        return "Success";
    }
}
