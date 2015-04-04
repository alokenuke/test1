<?php
namespace backend\controllers;

use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
/**
 * Class TagsController
 * @package rest\versions\v1\controllers
 */
class RolesController extends ApiController
{
    public $partialMatchFields;
        
    public function init() {
        $this->modelClass = 'backend\models\Roles';
        
        $this->partialMatchFields = ['type','role_name'];
        
        parent::init();
    }
    
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
        $behaviors['access'] = [
                'class' => \backend\models\RoleAccess::className(),
                'rules' => [
                    [
                        'actions' => ['search', 'getall', 'loadactions', 'create', 'update', 'view', 'delete'],
                        'allow' => true,
                        'roles' => ['Site', 'Client'],
                    ],
                    [
                        'actions' => ['get-permission'],
                        'allow' => true,
                        'roles' => ['Client', 'Site']
                    ]
                ]
        ];
        
        return $behaviors;
    }
    
    public function actionGetPermission() {
        if (!$_POST) {
            
            $post = $_GET['modules'];
            
            $roleDetails = \yii::$app->user->identity->role_details;
            $permissions = [];
            
            if($roleDetails->isAdmin) {
                $data = \backend\models\ModulesActions::find()->andWhere(['module_name' => $post, 'status' => 1])->all();
                
                foreach($data as $d)
                    $permissions[$d['action']] = true;
            }
            else {
                $data = \backend\models\RoleSettings::find()->andWhere(['role_id' => $roleDetails->id, 'module' => $post])->joinWith("roles")->one();
                
                foreach(json_decode($data['role_params']) as $k => $d) {
                    if($d==1)
                        $permissions[$k] = true; 
                }
            }
            
            return [$post => $permissions];
            
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
            
            $pageLimit = 20;
            if(isset($post['sort']))
                $_GET['sort'] = $post['sort'];
            if(isset($post['page']))
                $_GET['page'] = $post['page'];
            if(isset($post['limit']))
                $pageLimit = $post['limit'];
            
            try {
                $provider = new ActiveDataProvider ([
                    'query' => $query->andWhere(['company_id' => \yii::$app->user->identity->company_id])->andWhere(["isAdmin" => 0]),
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
    
    public function actionLoadactions() {
        if (!$_POST) {
            $post = \Yii::$app->request->post();
            
            $model = new \backend\models\ModulesActions();
            return $model->getModuleActions();
            
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
    }    
}
