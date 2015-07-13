<?php
namespace backend\controllers;

use yii;
use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
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
        //$this->checkRoleAccess("company", \yii::$app->requestedAction);
        
    }
    
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
        $behaviors['access'] = [
                'class' => \backend\models\RoleAccess::className(),
                'rules' => [
                    [
                        'actions' => ['search', 'stats', 'getall', 'savecompany', 'view', 'delete'],
                        'allow' => true,
                        'roles' => ['Site'],
                    ],
                    [
                        'actions' => ['default'],
                        'allow' => false,
                        'roles' => ['Site'],
                    ],
                    [
                        'actions' => ['default', 'databackup'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ]
        ];
        
        return $behaviors;
    }
    
    public function actionDefault() {
        if (!$_POST) {
            $post = \Yii::$app->request->post();
            
            $model = new $this->modelClass;
            
            $query = $model->find()->andWhere(['id' => \yii::$app->user->identity->company_id]);
            
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
    
    public function actionDatabackup() {
        if (!$_POST) {
            error_reporting(0);
            $company_id = Yii::$app->user->identity->company_id;
            
            $models = [new \backend\models\LabelTemplates(), new \backend\models\Projects(), new \backend\models\ProjectLevel(), new \backend\models\ProjectLevelProjects(), new \backend\models\RelatedTags(), new \backend\models\RelItemProcess(), new \backend\models\RelUserLevelsUsers(), new \backend\models\ReportTemplates(), new \backend\models\Roles(), new \backend\models\RoleSettings(), new \backend\models\Tags(), new \backend\models\TagActivityAttachment(), new \backend\models\TagActivityLog(), new \backend\models\TagAssignment(), new \backend\models\Items(), new \backend\models\ItemsProjects(), new \backend\models\TagProcess(), new \backend\models\TagProcessProjects(), new \backend\models\TagUserNotificationStatus(), new \backend\models\Timeattendance(), new \backend\models\TimeattendanceAssignment(), new \backend\models\TimeattendanceLog(), new \backend\models\User(), new \backend\models\UserGroups(), new \backend\models\UserGroupProjects(), new \backend\models\UserLevels()];
            
            $fileManager = new \backend\models\FileManager();
            $folderPath = $fileManager->getPath('databackup');
            
            foreach($models as $model) {
                $arrayHelper = new Yii\helpers\ArrayHelper();
                
                $tableName = $model->tableSchema->name;
                $data = $model->find()->all();
                
                $file = fopen($folderPath."/".$tableName.".csv","w");
                
                // Write Data
                foreach ($data as $key => $d)
                {
                    $tableData = $arrayHelper->toArray($d);
                    $rows = array_values($tableData);
                    if($key == 0) {
                        // Write Columns
                        try {
                            fputcsv($file, array_keys($tableData));
                        }
                        catch(Exception $ex) {
                            
                        }
                    }
                    fputcsv($file, $rows);
                }
            }
            
            // Get real path for our folder
            $rootPath = realpath($fileManager->getRootPath());
            
            $zipPath = './temp/backup-'.  time() .'.zip';
            
            // Initialize archive object
            $zip = new \ZipArchive();
            
            if(!$zip->open($zipPath, \ZipArchive::CREATE)) {
                die("Failed to create archive\n");
            }
            
            // Create recursive directory iterator
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($rootPath),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );
            foreach ($files as $name => $file) {
                // Get real path for current file
                $filePath = $file->getRealPath();
                
                $filename = $file->getFilename();
                $originalFile = preg_replace("/".addslashes($rootPath)."/", "", $filePath, 1);
                
                if(!$originalFile || $filename == "" || $filename == '.' || $filename == '..')
                    continue;
                // Add current file to archive
                $zip->addFile($filePath, preg_replace("/\\".DIRECTORY_SEPARATOR."/", "", $originalFile, 1));
            }

            // Zip archive will be created only after closing object
            $zip->close();
            
            \Yii::$app->getResponse()->sendFile($zipPath, "backup.zip");
            
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
            
            $return = [];
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
            $role = \backend\models\Roles::find()->where(['company_id' => $company->id, 'isAdmin' => 1])->one();
            
            if(!$role) {
                $role = new \backend\models\Roles();
                $role->role_name = 'Super Admin';
                $role->type = 'Client';
                $role->isAdmin = 1;
                $role->company_id = $company->id;
                $role->status = 1;
                $role->created_date = date('Y-m-d');
                $role->save();
            }
            if(isset($data['user']['id']) && $data['user']['id']>0) {
                $user = \backend\models\User::findOne($data['user']['id']);
                if(!$user->role)
                    $user->role = $role->id;
            }
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
            
            if(!isset($data['company']['id'])) {
                $company->sampleTagParams['user_id'] = $user->id;
                $company->createSampleTags($company->id);
            }
            
        } else {
            $transaction->rollBack();
            return $company;
        }
        
        $transaction->commit();
        return "Success";
    }
}
