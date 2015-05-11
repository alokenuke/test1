<?php
namespace backend\controllers;

use yii;
use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
use yii\base\Model;
use yii\bootstrap\ActiveForm;
use \backend\models\User;

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
    
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
        $behaviors['access'] = [
                'class' => \backend\models\RoleAccess::className(),
                'rules' => [
                    [
                        'actions' => ['search', 'exports', 'levelusers', 'stats', 'getall', 'multiinsert', 'create', 'update', 'view', 'delete'],
                        'allow' => true,
                        'roles' => ['Site', 'Client'],
                    ],
                    [
                        'actions' => ['change-password'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ]
        ];
        
        return $behaviors;
    }
    
    public function actionSearch() {
        if (!$_POST) {
            
            $post = \Yii::$app->request->post();
            
            $model = new User();
            
            $query = $model->find();
            
            if(isset($post['select']))
               $query->select($post['select']);

            if(isset($post['search'])) {
                foreach($post['search'] as $key => $val)
                    if($key=="name") {
                            $query->andWhere("first_name like :name OR last_name like :name", [':name' => "%$val%"]);
                    } else if( $key == "usergroups" && $val['id']){
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
            if(isset($post['excludeUserIds'])) {
                $query->andWhere(['not in', 'id', $post['excludeUserIds']]);
            }
            
            $pageLimit = 20;
            if(isset($post['sort'])) {
                $_GET['sort'] = $post['sort'];
                
                if($post['sort'] == 'name') {
                    $query->orderBy([
                            'first_name' => SORT_ASC,
                            'last_name' => SORT_ASC,
                        ]);
                }
                else if( $post['sort'] == '-name'){
                    $query->orderBy([
                        'first_name' => SORT_DESC,
                        'last_name' => SORT_DESC,
                    ]); 
                }
            }
            else
                $_GET['sort'] = "-id";
            if(isset($post['page']))
                $_GET['page'] = $post['page'];
            if(isset($post['limit']))
                $pageLimit = $post['limit'];
            
            try {
                $provider = new ActiveDataProvider ([
                    'query' => $query->andWhere(['user.company_id' => \yii::$app->user->identity->company_id])->andWhere(['<>', 'user.status', User::STATUS_DELETED]),
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
        return $this->getResourceCount();
    }
    
    public function getResourceCount() {
        $return = array();
        $return['projects']['count'] = \backend\models\Projects::find()->count();
        $return['tags']['count'] = \backend\models\Tags::find()->count();
        $return['users']['count'] = User::find()->andWhere(['user.status' => 1])->count();
        $return['items']['count'] = \backend\models\Items::find()->count();
        
        $fileManager = new \backend\models\FileManager();
        
        $rootPath = $fileManager->getRootPath();
        
        if(!file_exists($rootPath))
            mkdir ($rootPath);
        
        $return['space']['count'] = $this->getFolderSize($rootPath);
        
        $membership = \backend\models\Company::findOne($fileManager->company);
        
        $return['projects']['limit'] = ($membership->membership->limit_active_projects==-1?"Unlimited":$membership->membership->limit_active_projects." Allowed");
        $return['tags']['limit'] = ($membership->membership->limit_tags==-1?"Unlimited":$membership->membership->limit_tags." Allowed");
        $return['users']['limit'] = ($membership->membership->limit_users==-1?"Unlimited":$membership->membership->limit_users." Allowed");
        $return['items']['limit'] = ($membership->membership->limit_items==-1?"Unlimited":$membership->membership->limit_items." allowed");
        $return['space']['limit'] = ($membership->membership->limit_data==-1?"Unlimited":$this->format_size($membership->membership->limit_data, "MB")." Allowed");
        
        return $return;
    }
    
    public function getFolderSize($path) {
        
        $path = realpath($path);
        
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $obj = new \COM ( 'scripting.filesystemobject' );
                        
            if ( is_object ( $obj ) )
            {
                $ref = $obj->getfolder ( $path );
                $size = $ref->size;
                $obj = null;
            }
            else
            {
                echo 'can not create object';
            }
        } else {
            $io = popen ( '/usr/bin/du -sk ' . $path, 'r' );
            $size = fgets ( $io, 4096);
            $size = substr ( $size, 0, strpos ( $size, "\t" ) );
            pclose ( $io );
        }
        return $this->format_size($size);
    }
    
    public function format_size($size, $currentSize="") {
        $units = explode(' ', 'B KB MB GB TB PB');
        
        $mod = 1024;

        for ($i = 0; $size > $mod; $i++) {
            $size /= $mod;
        }

        $endIndex = strpos($size, ".")+3;
        
        if($currentSize) {
            $i += array_search($currentSize, $units);
        }

        return substr( $size, 0, $endIndex).' '.$units[$i];
    }
    
    public function actionLevelusers($id) {
        if (!$_POST) {
            
            $post = \Yii::$app->request->post();
            
            $model = new $this->modelClass;
            
            $query = $model->find()
                    ->leftJoin('rel_user_levels_users rel_ul', 'rel_ul.user_id=user.id')->andWhere(["rel_ul.user_level_id" => $id])->andWhere(['user.company_id' => \yii::$app->user->identity->company_id])->andWhere(['<>', 'user.status', User::STATUS_DELETED]);
            
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
            else
                $_GET['sort'] = "-created_date";
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
        
        $post = Yii::$app->request->post("User");
		
	$models = $this->loadMultiple($post);
        
        $validate = $this->validateMultiple($models);
                
        if (!count($validate)) {
            $hasError = false;
            foreach ($models as $key => $item) {
               // populate and save records for each model
                if ($item->save()) {
                    // do something here after saving
                    $validate['User'][$key]['id'] = $item->id;
                }
                else {
                    $hasError = true;
                    Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed.');   
                    
                    foreach ($item->getErrors() as $attribute => $errors) {
                        $validate['User'][$key][$attribute] = $errors;
                    }
                }
            }
            if($hasError)
                return $validate;
            else
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
    
	
    /**
     * Populates a set of models with the data from end user.
     * @return boolean whether the model is successfully populated with some data.
     */
    public function loadMultiple($data)
    {
        $company_id = \yii::$app->user->identity->company_id;
        $models = [];
        foreach ($data as $i => $d) {
            $models[$i] = new $this->modelClass;
            $models[$i]->setAttribute('company_id', $company_id);
            if(isset($d['id']) && $d['id']>0) {
                $existingUser = $models[$i]->find(['id' => $d['id'], 'status' => self::STATUS_ACTIVE])->one();
                if($existingUser)
                    $models[$i] = $existingUser;
            }
            
            $models[$i]->setAttributes($d);
        }
        return $models;
    }
    
    public function actionChangePassword()
    {        
        try {
            $model = new \backend\models\ChangePassword();
            
            if($model->load(\Yii::$app->request->post()) && $model->validate()) {
                $userObj = \yii::$app->user->identity;
                
                $userObj->setPassword($model->new_password);
                
                $userObj->save();
                
                return "Success";
            }
            else
                return $model;
            
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

    }
    
    public function actionExports() {
        if (!$_POST) {
            error_reporting(0);
            $post = \Yii::$app->request->post();
            
            $phpExcel = new \backend\models\GenerateExcel();
            
            $phpExcel->createWorksheet();
            $phpExcel->setDefaultFont('Calibri', 13);

            $default = array(
                array('label' => 'ID', 'width' => 'auto'),
                array('label' => 'First Name', 'width' => 'auto'),
                array('label' => 'Last Name', 'width' => 'auto'),
                array('label' => 'Username', 'width' => 'auto'),
                array('label' => 'Designation', 'width' => 'auto'),
                array('label' => 'Email', 'width' => 'auto'),
                array('label' => 'Role', 'width' => 'auto'),
                array('label' => 'Phone Number', 'width' => 'auto'),
                array('label' => 'Receive Notification', 'width' => 'auto'),
                array('label' => 'Allow BE', 'width' => 'auto'),
            );

            $phpExcel->addTableHeader($default, array('name' => 'Cambria', 'bold' => true));

            $phpExcel->setDefaultFont('Calibri', 12);
            
            $phpExcel->addTableFooter();
            /* * ******************************************** */

            //-> Create and add the sheets and also check if the form type is pre-defined or custom
            $index = 0;
            $files ;
            //foreach ($post as $data) {
                
                foreach ($post as $dat) {
                    
//                    $allow_be = ($dat['allow_be'])?'Yes':'No';
//                    $status = ($dat['project_status'])?'Active':'Inactive';
                    
                    $record = array(
                        $dat['id'],
                        $dat['first_name'],
                        $dat['last_name'],
                        $dat['username'],
                        $dat['designation'],
                        $dat['email'],
                        $dat['role'],
                        $dat['contact_number'],
                        $dat['rec_notification'],
                        $dat['allow_be'],
                        );
                    $phpExcel->addTableRow($record);
                }
            //}

            $phpExcel->addTableFooter();
            
            $filename = "temp/UserReports-". date("d-m-Y_").\yii::$app->session->id.".xlsx";
            $phpExcel->output($filename, false, 'S');
            return $filename;
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
    }

}
