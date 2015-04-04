<?php
namespace backend\controllers;

use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
/**
 * Class TagsController
 * @package rest\versions\v1\controllers
 */
class ImportsController extends ApiController
{
    
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
        $behaviors['access'] = [
                'class' => \backend\models\RoleAccess::className(),
                'rules' => [
                    [
                        'actions' => ['employee-logs', 'import-projects', 'users'],
                        'allow' => true,
                        'roles' => ['Client'],
                    ]
                ]
        ];
        
        return $behaviors;
    }
    
    public function actionEmployeeLogs() {
        if (!$_POST) {
            
            $post = \Yii::$app->request->post();
            
            $model = new \backend\models\UserTokens();
            
            $query = $model->find();
            
            if(isset($post['select']))
               $query->select($post['select']);

            if(isset($post['search'])) {
                foreach($post['search'] as $key => $val)
                    if(isset($val)) {
                        if($key=="date_range") {
                            if(isset($val['from_date']) && isset($val['to_date'])) {
                                $val['from_date'] = strtotime($val['from_date']);
                                $val['to_date'] = strtotime($val['to_date'])+86399;
                                $query->andWhere(['between', 'created_on', $val['from_date'], $val['to_date']]);
                            }
                            else if(isset($val['from_date'])) {
                                $val['from_date'] = strtotime($val['from_date']);
                                $query->andWhere(['>=', 'created_on', $val['from_date']]);
                            }
                            else if(isset($val['to_date'])) {
                                $val['to_date'] = strtotime($val['to_date'])+86399;
                                $query->andWhere(['<=', 'created_on', $val['to_date']]);
                            }
                        }
                        else if($key=="employee_name") {
                            $query->joinWith("user");
                            $query->andWhere("user.first_name LIKE :name OR user.last_name LIKE :name", ['name' => "%$val%"]);
                        }
                        else if(in_array($key, $this->partialMatchFields))
                            $query->andWhere(['like', $key, $val]);
                        else
                            $query->where([$key => $val]);
                    }
            }
            
            $pageLimit = 20;
            if(isset($post['sort']))
                $_GET['sort'] = $post['sort'];
            if(isset($post['page']))
                $_GET['page'] = $post['page'];
            if(isset($post['limit']))
                $pageLimit = $post['limit'];
            
            if(isset($post['da']))
                $pageLimit = 'all';
            
            
            try {
                $provider = new ActiveDataProvider ([
                    'query' => $query->orderBy("created_on DESC"),
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
    
   public function actionImportProjects(){
       if (!$_POST) {
            error_reporting(0);
            $post = \Yii::$app->request->post('data');
            $post = $post['project_files'];
            $objReader = \PHPExcel_IOFactory::createReaderForFile($post);
            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load($post);
            
            $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
            $highestRow = $objWorksheet->getHighestRow();
            $highestColumn = $objWorksheet->getHighestColumn();

//            $headingsArray = $objWorksheet->rangeToArray('A1:'.$highestColumn.'1',null, true, true, true);
//            $headingsArray = $headingsArray[1];
            
            $headingsArray = [
                'A'=>'id',
                'B'=>'project_name',
                'C'=>'project_location',
                'D'=>'project_manager',
                'E'=>'project_director',
                'F'=>'client_name',
                'G'=>'client_project_manager',
                'H'=>'main_contractor',
                'I'=>'consultant',
                'J'=>'consultant_project_manager',
                'K'=>'about',
                'L'=>'project_address',
                'M'=>'project_city',
                'N'=>'project_country',
                'O'=>'timezone_id',
                'P'=>'timezone_name',
                'Q'=>'client_address',
                'R'=>'client_city',
                'S'=>'client_country',
            ];



            $r = -1;
            $namedDataArray = array();
            
            $connection = \Yii::$app->db;
            $transaction = $connection->beginTransaction();  
            
            for ($row = 2; $row <= $highestRow; ++$row) {
                $dataRow = $objWorksheet->rangeToArray('A'.$row.':'.$highestColumn.$row,null, true, true, true);
                //if ((isset($dataRow[$row]['A'])) && ($dataRow[$row]['A'] > '')) {
                    ++$r;
                    foreach($headingsArray as $columnKey => $columnHeading) {
                        //$namedDataArray[$r][$columnHeading] = $dataRow[$row][$columnKey];
                        $namedDataArray[$columnHeading] = $dataRow[$row][$columnKey];
                    }
                    if($namedDataArray['id']>0) 
                        $model = \backend\models\Projects::findOne(['id' => $namedDataArray['id']]);
                    else
                        $model = new \backend\models\Projects();
                    
                    $model->setAttributes($namedDataArray);
                    if(!$model->save()){
                      $transaction->rollBack();
                      return $model;
                    }
                    
                //}
            }
            $transaction->commit();
            return 'success';
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
   }
   
   public function actionImportUsers(){
       if (!$_POST) {
            error_reporting(0);
            $post = \Yii::$app->request->post('data');
            
            $post = $post['user_files'];
            $objReader = \PHPExcel_IOFactory::createReaderForFile($post);
            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load($post);
            
            $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
            $highestRow = $objWorksheet->getHighestRow();
            $highestColumn = $objWorksheet->getHighestColumn();

//            $headingsArray = $objWorksheet->rangeToArray('A1:'.$highestColumn.'1',null, true, true, true);
//            $headingsArray = $headingsArray[1];
            
            $headingsArray = [
                'A'=>'id',
                'B'=>'first_name',
                'C'=>'last_name',
                'D'=>'username',
                'E'=>'designation',
                'F'=>'email',
                'G'=>'role',
                'H'=>'contact_number',
                'I'=>'receive_notification',
                'J'=>'allow_be'
            ];



            $r = -1;
            $namedDataArray = array();
            
            $connection = \Yii::$app->db;
            $transaction = $connection->beginTransaction();  
            
            for ($row = 2; $row <= $highestRow; ++$row) {
                $dataRow = $objWorksheet->rangeToArray('A'.$row.':'.$highestColumn.$row,null, true, true, true);
                //if ((isset($dataRow[$row]['A'])) && ($dataRow[$row]['A'] > '')) {
                    ++$r;
                    foreach($headingsArray as $columnKey => $columnHeading) {
                        //$namedDataArray[$r][$columnHeading] = $dataRow[$row][$columnKey];
                        $namedDataArray[$columnHeading] = $dataRow[$row][$columnKey];
                    }
                    if($namedDataArray['id']>0) 
                        $model = \backend\models\User::findOne(['id' => $namedDataArray['id']]);
                    else
                        $model = new \backend\models\User();
                    
                    $model->setAttributes($namedDataArray);
                    if(!$model->save()){
                        \yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed.');
                        $transaction->rollBack();
                        return ['row' => $row, 'error' => $model->getErrors()];
                    }
                    
                //}
            }
            $transaction->commit();
            return 'success';
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
   }
    
}
