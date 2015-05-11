<?php
namespace backend\controllers;

use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
/**
 * Class TagsController
 * @package rest\versions\v1\controllers
 */
class ReportsController extends ApiController
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
                        'actions' => ['search', 'labels', 'timeattendancelabels', 'generate-tag-reports', 'generate-tag-reports-by-id', 'generate-time-attendance-reports', 'generate-user-reports', 'generate-employee-logs-reports'],
                        'allow' => true,
                        'roles' => ['Client'],
                    ],
                    [
                        'actions' => ['employee-logs'],
                        'allow' => true,
                        'roles' => ['Client', 'Site'],
                    ],
                ]
        ];
        
        return $behaviors;
    }
    
    // Get list of tags for label printing
    public function actionLabels() {
        if (!$_POST) {
            
            $_GET['expand'] = "project_level, itemObj, userGroup,processObj";
            
            $post = \Yii::$app->request->post();
            
            $model = new $this->modelClass;
            
            $query = $model->find();
            
            if(isset($post['search'])) {
                foreach($post['search'] as $key => $val) {
                    if($key=="date_range") {
                        if(isset($val['from_date']) && isset($val['to_date'])) {
                            $val['from_date'] = date("Y-m-d H:i:s", strtotime($val['from_date']));
                            $val['to_date'] = date("Y-m-d", strtotime($val['to_date']));
                            $query->andWhere(['between', 'created_date', $val['from_date'], $val['to_date']]);
                        }
                        else if(isset($val['from_date'])) {
                            $val['from_date'] = date("Y-m-d", strtotime($val['from_date']));
                            $query->andWhere(['>=', 'created_date', $val['from_date']]);
                        }
                        else if(isset($val['to_date'])) {
                            $val['to_date'] = date("Y-m-d", strtotime($val['to_date']));
                            $query->andWhere(['<=', 'created_date', $val['to_date']]);
                        }
                    }
                    else if(is_array ($val)) {
                        if(isset($val['project']))
                            $query->where(['project_id' => $val['project']['id']]);
                    }
                    else if(isset($val)) {
                        if(in_array($key, $this->partialMatchFields))
                            $query->andWhere(['like', $key, $val]);
                        else
                            $query->andWhere([$key => $val]);
                    }
                }
            }
            
            $pageLimit = 20;
            
            try {
                $provider = new ActiveDataProvider ([
                    'query' => $query,
                    'pagination'=> false,
                ]);
            } catch (Exception $ex) {
                throw new \yii\web\HttpException(500, 'Internal server error');
            }
            return $provider;
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
    }
    
    // Get list of tags for label printing
    public function actionTimeattendancelabels() {
        if (!$_POST) {
            
            $_GET['expand'] = "project_level, processObj";
            
            $post = \Yii::$app->request->post();
            
            $model = new \backend\models\Timeattendance();
            
            $query = $model->find();
            
            if(isset($post['search'])) {
                foreach($post['search'] as $key => $val) {
                    if($key=="date_range") {
                        if(isset($val['from_date']) && isset($val['to_date'])) {
                            $val['from_date'] = date("Y-m-d H:i:s", strtotime($val['from_date']));
                            $val['to_date'] = date("Y-m-d", strtotime($val['to_date']));
                            $query->andWhere(['between', 'created_date', $val['from_date'], $val['to_date']]);
                        }
                        else if(isset($val['from_date'])) {
                            $val['from_date'] = date("Y-m-d", strtotime($val['from_date']));
                            $query->andWhere(['>=', 'created_date', $val['from_date']]);
                        }
                        else if(isset($val['to_date'])) {
                            $val['to_date'] = date("Y-m-d", strtotime($val['to_date']));
                            $query->andWhere(['<=', 'created_date', $val['to_date']]);
                        }
                    }
                    else if(is_array ($val)) {
                        if(isset($val['project']))
                            $query->where(['project_id' => $val['project']['id']]);
                    }
                    else if(isset($val)) {
                        if(in_array($key, $this->partialMatchFields))
                            $query->andWhere(['like', $key, $val]);
                        else
                            $query->andWhere([$key => $val]);
                    }
                }
            }
            
            $pageLimit = 20;
            
            try {
                $provider = new ActiveDataProvider ([
                    'query' => $query,
                    'pagination'=> false,
                ]);
            } catch (Exception $ex) {
                throw new \yii\web\HttpException(500, 'Internal server error');
            }
            return $provider;
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
                            $query->where([$key => $val]);
                    }
            }
                       
            if(isset($post['excludeProjects'])) {
                $projectIds = [];
                foreach($post['excludeProjects'] as $project)
                    $projectIds[] = $project['id'];
                
                $query->andWhere(['not in', 'id', $projectIds]);
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
    
    public function actionEmployeeLogs() {
        if (!$_POST) {
            
            $post = \Yii::$app->request->post();
            
            $model = new \backend\models\UserTokens();
            
            $query = $model->find();
            
            if(isset($post['select']))
               $query->select($post['select']);
            
            $query->joinWith("user");
            $query->andWhere(['user.company_id' => \yii::$app->user->identity->company_id]);

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
                            $query->andWhere("user.first_name LIKE :name OR user.last_name LIKE :name", ['name' => "%$val%"]);
                        } else if($key=="username") {
                            $query->andWhere("user.username LIKE :username", ['username' => "%$val%"]);
                        }else if($key=="email") {
                            $query->andWhere("user.email LIKE :email", ['email' => "%$val%"]);
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
    
    public function actionGenerateTagReports() {
        if (!$_POST) {
            error_reporting(0);
            $post = \Yii::$app->request->post();
            
            $_GET['expand'] = "project_level,itemObj,tagActivityLog";
            
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
            
            try {
                $provider = new ActiveDataProvider ([
                    'query' => $query                        
                ]);
            } catch (Exception $ex) {
                throw new \yii\web\HttpException(500, 'Internal server error');
            }
            
            $serializer = new \backend\models\CustomSerializer();
            
            $global_task_array = $serializer->serialize($provider);
            
            $phpExcel = new \backend\models\GenerateExcel();
            
            $phpExcel->createWorksheet();
            $phpExcel->setDefaultFont('Calibri', 13);

            $default = array(
                array('label' => 'Sr.', 'width' => 'auto'),
                array('label' => 'Type', 'width' => 'auto'),
                array('label' => 'UID', 'width' => 'auto'),
                array('label' => 'Tag Name', 'width' => 'auto'),
                array('label' => 'Project Level', 'width' => 'auto'),
                array('label' => 'Item Type', 'width' => 'auto'),
                array('label' => 'Tag Description', 'width' => 'auto'),
                array('label' => 'Product Code', 'width' => 'auto'),
                array('label' => 'Last Status', 'width' => 'auto'),
                array('label' => 'Created Date', 'width' => 'auto')
            );

            $phpExcel->addTableHeader($default, array('name' => 'Cambria', 'bold' => true));

            $i = 1;
            $phpExcel->setDefaultFont('Calibri', 12);
            foreach ($global_task_array as $raw) {
                $itemName = "";
                foreach($raw['itemObj'] as $key => $item) {
                    if($key)
                        $itemName .= " > ";
                    $itemName .= $item['item_name'];
                }
                $lastStatus = "Not Started Yet";
                if($raw['tagActivityLog']['stageInfo'])
                    $lastStatus = $raw['tagActivityLog']['stageInfo']['process_name']." - ".(is_object($raw['tagActivityLog']['answer']?$raw['tagActivityLog']['answer']['process_name']:$raw['tagActivityLog']['answer']))."\n By:".$raw['tagActivityLog']['loggedBy']['first_name']." ".$raw['tagActivityLog']['loggedBy']['last_name']."\n at ".$raw['tagActivityLog']['logged_date'];
                
                $data = array(
                    $i++,
                    $raw['type'],
                    $raw['uid'],
                    $raw['tag_name'],
                    implode(" > ", $raw['project_level']),
                    $itemName,
                    $raw['tag_description'],
                    $raw['product_code'],
                    $lastStatus,
                    $raw['created_date'],
                );
                $phpExcel->addTableRow($data);
            }
            
            $phpExcel->addTableFooter();
            /* * ******************************************** */

            //-> Create and add the sheets and also check if the form type is pre-defined or custom
            $s = 1;
            foreach ($global_task_array as $task) {
                if ($task['Task']['form_type'] == 'drawing') {// if condition end here
                   if(isset($task['Drawing']) && (count($task['Drawing']) > 0)){ 
                    //define table cells
                    $pre_default = array(
                        array('label' => ('Id.'), 'width' => '10'),
                        array('label' => ('Employee'), 'width' => '18'),
                        array('label' => ('Revision'), 'width' => '18'),
                        array('label' => ('Status'), 'width' => '18'),
                        array('label' => ('Time'), 'width' => '18'),
                        array('label' => ('Comment'), 'width' => '20')
                    );
                    $mysheet = $phpExcel->addSheet($task['Task']['unique_code'], $s++);
                // heading
                    $phpExcel->addTableHeader($pre_default, array('name' => 'Cambria', 'bold' => true));
                    $phpExcel->setDefaultFont('Calibri', 12);
                    if (isset($task['Drawing'])) {
                        foreach ($task['Drawing'] as $index => $raw) {
                            $data = array(
                                $index + 1,
                                $raw['DrawingLog']['emp_name'],
                                $raw['DrawingLog']['revision'],
                                $raw['DrawingLog']['status'],
                                $this->Time->format('d M Y h:iA', $raw['DrawingLog']['created'], null, $task['Timezone']['name']) . ' (' . $task['Timezone']['name'] . ')',
                                $raw['DrawingLog']['comment'],
                            );
                            $phpExcel->addTableRow($data);
                        }
                    }
                    $phpExcel->addTableFooter();
                   }
                }
            }
            $filename = "temp/TagReport-". date("d-m-Y_").\yii::$app->session->id.".xlsx";
            $phpExcel->output($filename, false, 'S');
            return $filename;
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
    }
    
    public function actionGenerateTagReportsById() {
        if (!$_POST) {
            error_reporting(0);
            $post = \Yii::$app->request->post();
            
            $phpExcel = new \backend\models\GenerateExcel();
            
            $phpExcel->createWorksheet();
            $phpExcel->setDefaultFont('Calibri', 13);

            $default = array(
                array('label' => 'Sr.', 'width' => 'auto'),
                array('label' => 'Status', 'width' => 'auto'),
                array('label' => 'Comments', 'width' => 'auto'),
                array('label' => 'Attachments', 'width' => 'auto'),
                array('label' => 'Activity Location', 'width' => 'auto'),
                array('label' => 'Logged By', 'width' => 'auto')
            );

            $phpExcel->addTableHeader($default, array('name' => 'Cambria', 'bold' => true));

            $phpExcel->setDefaultFont('Calibri', 12);
            
            $phpExcel->addTableFooter();
            /* * ******************************************** */

            //-> Create and add the sheets and also check if the form type is pre-defined or custom
            $index = 0;
            $files ;
            foreach ($post as $data) {
                
                foreach ($data as $dat) {
                    if (isset($dat['attachments']) && is_array($dat['attachments'])) {

                        foreach ($dat['attachments'] as $attach) {
                            $files .= $attach['filename'] . "\n";
                        }
                    } else {
                        $files = 'No attachments available';
                    }

                    $record = array(
                        ++$index,
                        $dat['stageInfo']['process_name'] . ' ' . $dat['answer']['rocess_name'],
                        $dat['comment'],
                        $files,
                        $dat['location'] . "\n" . $dat['device'],
                        $dat['user']['first_name'] . ' ' . $dat['user']['last_name'] . "\n" .
                        $dat['logged_date']
                    );
                    $phpExcel->addTableRow($record);
                }
            }

            $phpExcel->addTableFooter();
            
            $filename = "temp/TagReportByID-". date("d-m-Y_").\yii::$app->session->id.".xlsx";
            $phpExcel->output($filename, false, 'S');
            return $filename;
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
    }
    
    public function actionGenerateTimeAttendanceReports() {
        if (!$_POST) {
            error_reporting(0);
            $post = \Yii::$app->request->post();
            
            $_GET['expand'] = "user,timeattendance,project_level";
            
            $model = new \backend\models\TimeattendanceLog();
            
            $query = $model->find();
            
            if(isset($post['select']))
               $query->select($select);
            
            $query->joinWith("timeattendance");

            if(isset($post['search'])) {
                foreach($post['search'] as $key => $val)
                    if(isset($val)) {
                        if($key=="date_range") {
                            if(isset($val['from_date']) && isset($val['to_date'])) {
                                $val['from_date'] = date("Y-m-d H:i:s", strtotime($val['from_date']));
                                $val['to_date'] = date("Y-m-d", strtotime($val['to_date'])+86399);
                                $query->andWhere(['between', 'created_date', $val['from_date'], $val['to_date']]);
                            }
                            else if(isset($val['from_date'])) {
                                $val['from_date'] = date("Y-m-d", strtotime($val['from_date']));
                                $query->andWhere(['>=', 'created_date', $val['from_date']]);
                            }
                            else if(isset($val['to_date'])) {
                                $val['to_date'] = date("Y-m-d", strtotime($val['to_date'])+86399);
                                $query->andWhere(['<=', 'created_date', $val['to_date']]);
                            }
                        }
                        else if($key=='usergroup') {
                            $query->andWhere(['user_group_id' => $val['id']]);
                        }
                        else if($key=="employee_name") {
                            $query->joinWith("user");
                            $query->andWhere("user.first_name LIKE :name OR user.last_name LIKE :name", ['name' => "%$val%"]);
                        }
                        else if(in_array($key, $this->partialMatchFields))
                            $query->andWhere(['like', $key, $val]);
                        else
                            $query->andWhere([$key => $val]);
                    }
            }
            
            try {
                $provider = new ActiveDataProvider ([
                    'query' => $query                        
                ]);
            } catch (Exception $ex) {
                throw new \yii\web\HttpException(500, 'Internal server error');
            }
            
            $serializer = new \backend\models\CustomSerializer();
            
            $global_task_array = $serializer->serialize($provider);
                        
            $phpExcel = new \backend\models\GenerateExcel();
            
            $phpExcel->createWorksheet();
            $phpExcel->setDefaultFont('Calibri', 13);

            $default = array(
                array('label' => 'Sr.', 'width' => 'auto'),
                array('label' => 'Project Name', 'width' => 'auto'),
                array('label' => 'UID', 'width' => 'auto'),
                array('label' => 'Tag Name', 'width' => 'auto'),
                array('label' => 'Tag Description', 'width' => 'auto'),
                array('label' => 'Project Level', 'width' => 'auto'),
                array('label' => 'Employee Name', 'width' => 'auto'),
                array('label' => 'Geolocation', 'width' => 'auto'),
                array('label' => 'Login Time', 'width' => 'auto'),
                array('label' => 'Logout Time', 'width' => 'auto'),
                array('label' => 'Hours Logged', 'width' => 'auto')
            );

            $phpExcel->addTableHeader($default, array('name' => 'Cambria', 'bold' => true));

            $i = 1;
            $phpExcel->setDefaultFont('Calibri', 12);
            foreach ($global_task_array as $raw) {
                
                $location = json_decode($raw['location']);
                
                $data = array(
                    $i++,
                    $raw['timeattendance']['project_name'],
                    $raw['timeattendance']['uid'],
                    $raw['timeattendance']['tag_name'],
                    $raw['timeattendance']['tag_description'],
                    implode(" > ", $raw['project_level']),
                    $raw['user']['first_name']." ".$raw['user']['last_name'],
                    "Latitude: ".$location['lat']."\n"."Longitude: ".$location['long'],
                    $raw['login_time'],
                    $raw['logout_time'],
                    $raw['hours_logged'],
                );
                $phpExcel->addTableRow($data);
            }
            
            $phpExcel->addTableFooter();
            /* * ******************************************** */

            //-> Create and add the sheets and also check if the form type is pre-defined or custom
            $s = 1;
//            foreach ($global_task_array as $task) {
//                if ($task['Task']['form_type'] == 'drawing') {// if condition end here
//                   if(isset($task['Drawing']) && (count($task['Drawing']) > 0)){ 
//                    //define table cells
//                    $pre_default = array(
//                        array('label' => ('Id.'), 'width' => '10'),
//                        array('label' => ('Employee'), 'width' => '18'),
//                        array('label' => ('Revision'), 'width' => '18'),
//                        array('label' => ('Status'), 'width' => '18'),
//                        array('label' => ('Time'), 'width' => '18'),
//                        array('label' => ('Comment'), 'width' => '20')
//                    );
//                    $mysheet = $phpExcel->addSheet($task['Task']['unique_code'], $s++);
//                // heading
//                    $phpExcel->addTableHeader($pre_default, array('name' => 'Cambria', 'bold' => true));
//                    $phpExcel->setDefaultFont('Calibri', 12);
//                    if (isset($task['Drawing'])) {
//                        foreach ($task['Drawing'] as $index => $raw) {
//                            $data = array(
//                                $index + 1,
//                                $raw['DrawingLog']['emp_name'],
//                                $raw['DrawingLog']['revision'],
//                                $raw['DrawingLog']['status'],
//                                $this->Time->format('d M Y h:iA', $raw['DrawingLog']['created'], null, $task['Timezone']['name']) . ' (' . $task['Timezone']['name'] . ')',
//                                $raw['DrawingLog']['comment'],
//                            );
//                            $phpExcel->addTableRow($data);
//                        }
//                    }
//                    $phpExcel->addTableFooter();
//                   }
//                }
//            }
            $filename = "temp/TimeAttendanceReport-". date("d-m-Y_").\yii::$app->session->id.".xlsx";
            $phpExcel->output($filename, false, 'S');
            return $filename;
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
    }


	public function actionGenerateProjectReports() {
        if (!$_POST) {
            error_reporting(0);
            $post = \Yii::$app->request->post();
            
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

            //-> Create and add the sheets and also check if the form type is pre-defined or custom
            $index = 0;
            $files ;
            //foreach ($post as $data) {
                
                foreach ($post as $dat) {
                    
                    
                    $timezone =  \backend\models\Timezones::findOne($dat['timezone_id']);
                    $record = array(
                        $dat['id'],
                        $dat['project_name'],
                        $dat['project_location'].','.$dat['project_city'],
                        $dat['project_manager'],
                        $dat['project_director'],
                        $dat['client_name'],
                        $dat['client_project_manager'],
                        $dat['main_contractor'],
                        $dat['consultant'],
                        $dat['consultant_project_manager'],
                        $dat['about'],
                        $dat['project_address'],
                        $dat['project_city'],
                        $dat['project_country'],
                        $timezone->id,
                        $timezone->name,
                        $dat['client_address'],
                        $dat['client_city'],
                        $dat['client_country']
                        );
                    $phpExcel->addTableRow($record);
                }
            //}

            $phpExcel->addTableFooter();
            
            $filename = "temp/ProjectReport-". date("d-m-Y_").\yii::$app->session->id.".xlsx";
            $phpExcel->output($filename, false, 'S');
            return $filename;
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
    }
    
    public function actionGenerateUserReports() {
        if (!$_POST) {
            error_reporting(0);
            $post = \Yii::$app->request->post();
            
            $phpExcel = new \backend\models\GenerateExcel();
            
            $phpExcel->createWorksheet();
            $phpExcel->setDefaultFont('Calibri', 13);

            $default = array(
                array('label' => 'Sr.', 'width' => 'auto'),
                array('label' => 'Name', 'width' => 'auto'),
                array('label' => 'Username', 'width' => 'auto'),
                array('label' => 'Designation', 'width' => 'auto'),
                array('label' => 'Email', 'width' => 'auto'),
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
                    
                    $allow_be = ($dat['allow_be'])?'Yes':'No';
                    $status = ($dat['project_status'])?'Active':'Inactive';
                    
                    $record = array(
                        ++$index,
                        $dat['first_name'].' '.$dat['last_name'],
                        $dat['username'],
                        $dat['designation'],
                        $dat['email'],
                        $dat['contact_number'],
                        $dat['rec_notification'],
                        $allow_be,
                        );
                    $phpExcel->addTableRow($record);
                }
            //}

            $phpExcel->addTableFooter();
            
            $filename = "temp/ProjectReport-". date("d-m-Y_").\yii::$app->session->id.".xlsx";
            $phpExcel->output($filename, false, 'S');
            return $filename;
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
            
            $filename = "temp/ProjectReport-". date("d-m-Y_").\yii::$app->session->id.".xlsx";
            $phpExcel->output($filename, false, 'S');
            return $filename;
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
    }
}
