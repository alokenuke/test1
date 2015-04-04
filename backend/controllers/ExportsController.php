<?php
namespace backend\controllers;

use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
/**
 * Class TagsController
 * @package rest\versions\v1\controllers
 */
class ExportsController extends ApiController
{
    public $partialMatchFields;
        
    public function init() {
        $this->modelClass = 'backend\models\Tags';
        
        $this->partialMatchFields = ['tag_name', 'tag_description', 'uid', 'product_code'];
        
        parent::init();
    }
    
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
        $behaviors['access'] = [
                'class' => \backend\models\RoleAccess::className(),
                'rules' => [
                    [
                        'actions' => ['employee-logs', 'generate-user-reports', 'generate-employee-logs-reports', 'download-project-template', 'download-user-template'],
                        'allow' => true,
                        'roles' => ['Client'],
                    ]
                ]
        ];
        
        return $behaviors;
    }
    
    // Get list of tags for label printing
    
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
    
    public function actionGenerateEmployeeLogsReports() {
        if (!$_POST) {
            error_reporting(0);
            $post = \Yii::$app->request->post();
            
            $phpExcel = new \backend\models\GenerateExcel();
            
            $phpExcel->createWorksheet();
            $phpExcel->setDefaultFont('Calibri', 13);

            $default = array(
                array('label' => 'Sr.', 'width' => 'auto'),
                array('label' => 'Employee Name', 'width' => 'auto'),
                array('label' => 'Username', 'width' => 'auto'),
                array('label' => 'Geolocation', 'width' => 'auto'),
                array('label' => 'Login Time', 'width' => 'auto'),
                array('label' => 'Status', 'width' => 'auto'),
            );

            $phpExcel->addTableHeader($default, array('name' => 'Cambria', 'bold' => true));

            $phpExcel->setDefaultFont('Calibri', 12);
            
            $phpExcel->addTableFooter();
            /* * ******************************************** */

            //-> Create and add the sheets and also check if the form type is pre-defined or custom
            $index = 0;
            $files ;
            
            //print_r($post);die;
            //foreach ($post as $data) {
                
                foreach ($post as $dat) {
                    
                        $name = $dat['user']['first_name'].' '.$dat['user']['last_name'];
                        $username = $dat['user']['username'];
                    
                    $time = time();
                    if( $dat['expire_on'] > $time && $dat['expiry_status'] == 0 ){
                        $status = 'Active';
                    } else if ( $dat['expire_on'] <= $time || $dat['expiry_status'] == 1){
                        $status = 'Logged Out';
                    }
                        
                    $record = array(
                        ++$index,
                        $name,
                        $username,
                        $dat['login_location']."\n(".$dat['request_from'].')',
                        date('d M y H:i:s',  strtotime($dat['created_on'])),
                        $status
                        );
                    $phpExcel->addTableRow($record);
                }
            //}

            $phpExcel->addTableFooter();
            
            $filename = "temp/EmployeeLogs-". date("d-m-Y_").\yii::$app->session->id.".xlsx";
            $phpExcel->output($filename, false, "S");
            return $filename;
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
    }
    
    public function actionDownloadProjectTemplate() {
        if (!$_POST) {
            error_reporting(0);
            
            $phpExcel = new \backend\models\GenerateExcel();
            
            $phpExcel->createWorksheet();
            $phpExcel->setDefaultFont('Calibri', 13);

            $default = array(
                array('label' => 'ID', 'width' => 'auto'),
                array('label' => 'Project Name', 'width' => 'auto'),
                array('label' => 'Location', 'width' => 'auto'),
                array('label' => 'Project Manager', 'width' => 'auto'),
                array('label' => 'Project Director', 'width' => 'auto'),
                array('label' => 'Client Name', 'width' => 'auto'),
                array('label' => 'Client-Project Manager', 'width' => 'auto'),
                array('label' => 'Contractor', 'width' => 'auto'),
                array('label' => 'Consultant', 'width' => 'auto'),
                array('label' => 'Consultant-Project Manager', 'width' => 'auto'),
                array('label' => 'Description', 'width' => 'auto'),
                array('label' => 'Project Address/Location', 'width' => 'auto'),
                array('label' => 'Project City', 'width' => 'auto'),
                array('label' => 'Project Country', 'width' => 'auto'),
                array('label' => 'Timezone ID', 'width' => 'auto'),
                array('label' => 'Timezone Name', 'width' => 'auto'),
                array('label' => 'Client Address', 'width' => 'auto'),
                array('label' => 'Client City', 'width' => 'auto'),
                array('label' => 'Client Country', 'width' => 'auto'),
            );

            $phpExcel->addTableHeader($default, array('name' => 'Cambria', 'bold' => true));

            $phpExcel->setDefaultFont('Calibri', 12);
            
            $phpExcel->addTableFooter();
            /* * ******************************************** */

            $phpExcel->addTableFooter();
            
            $filename = "ProjectTemplate-". date("d-m-Y_").\yii::$app->session->id.".xlsx";
            $phpExcel->output($filename);
            //return $filename;
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
    }
    
    public function actionDownloadUserTemplate() {
        if (!$_POST) {
            error_reporting(0);
            
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

            $phpExcel->addTableFooter();
            
            $filename = "UserTemplate-". date("d-m-Y_").\yii::$app->session->id.".xlsx";
            $phpExcel->output($filename);
            //return $filename;
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
    }
}
