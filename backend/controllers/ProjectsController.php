<?php
namespace backend\controllers;

use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
use backend\models\Tags;

/**
 * Class TagsController
 * @package rest\versions\v1\controllers
 */
class ProjectsController extends ApiController
{
    public $partialMatchFields;
        
    public function init() {
        $this->modelClass = 'backend\models\Projects';
        
        $this->partialMatchFields = ['project_name', 'client_project_manager', 'project_location', 'project_director', 'consultant', 'main_contractor', 'project_manager', 'project_address','project_city', 'client_address','client_city',];
        
        parent::init();
    }
    
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
        $behaviors['access'] = [
                'class' => \backend\models\RoleAccess::className(),
                'rules' => [
                    [
                        'actions' => ['search', 'export', 'projectsbyprocess', 'usergroup', 'projectsbylevel', 'getchartstats', 'tagitems', 'getall', 'index', 'create', 'update', 'view', 'delete'],
                        'allow' => true,
                        'roles' => ['Client'],
                    ]
                ]
        ];
        
        return $behaviors;
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
    
    public function actionUsergroup($id) {
        if (!$_POST) {
            
            $post = \Yii::$app->request->post();
            
            $model = new $this->modelClass;
            
            $query = $model->find()
                    ->leftJoin('user_group_projects group_projects', 'group_projects.project_id=projects.id')->andWhere(["group_projects.user_group_id" => $id]);
            
            if(isset($post['search'])) {
                foreach($post['search'] as $key => $val)
                    if(isset($val)) {
                        if(in_array($key, $this->partialMatchFields))
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
    
    // Get list of projects assigned to a project level
    public function actionProjectsbylevel($id) {
        if (!$_POST) {
            
            $post = \Yii::$app->request->post();
            
            $model = new $this->modelClass;
            
            $query = $model->find()
                    ->leftJoin('project_level_projects level_projects', 'level_projects.project_id=projects.id')->andWhere(["level_projects.level_id" => $id]);
            
            if(isset($post['search'])) {
                foreach($post['search'] as $key => $val)
                    if(isset($val)) {
                        if(in_array($key, $this->partialMatchFields))
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
    
    // Get list of projects assigned to a tag process
    public function actionProjectsbyprocess($id) {
        if (!$_POST) {
            
            $post = \Yii::$app->request->post();
            
            $model = new $this->modelClass;
            
            $query = $model->find()
                    ->leftJoin('tag_process_projects process_projects', 'process_projects.project_id=projects.id')->andWhere(["process_projects.process_id" => $id]);
            
            if(isset($post['search'])) {
                foreach($post['search'] as $key => $val)
                    if(isset($val)) {
                        if(in_array($key, $this->partialMatchFields))
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
    
    public function actionGetchartstats() {
        if (!$_POST) {
            
            $post = \Yii::$app->request->post();
            
            if(isset($post['project']) && $post['project'] > 0) {
                $duration = "daily";
                if(isset($post['duration']) && $post['duration'])
                    $duration = $post['duration'];
                
                $result = [];
                
                $range = range(5, 0);
                
                $result['completedTags'] = [];
                $result['totalTags'] = [];

                foreach($range as $r) {
                    
                    if($duration=='quarterly')
                    {
                        $quarterDate = $this->getQuarter(strtotime("-".($r*3)." months"));
                        $timestamp = strtotime($quarterDate['end']);
                        $result['labels'][] = $quarterDate['version'];
                    }
                    else if($duration=='daily')
                    {
                        $timestamp = strtotime("-$r day");
                        $result['labels'][] = date("D d M, Y", $timestamp);
                    }
                    else {
                        $timestamp = strtotime("-$r ".  str_replace("ly", "", $duration));
                        
                        $result['labels'][] = date("D d M, Y", $timestamp);
                    }
                    
                    $date = date("Y-m-d 23:59:59", $timestamp);
                    
                    $result['completedTags'][] = Tags::find()->andWhere(['completed' => '1', 'project_id' => $post['project']])->andWhere(['<=', 'completed_date', $date])->count();

                    $result['totalTags'][] = Tags::find()->andWhere(['project_id' => $post['project']])->andWhere(['<=', 'tags.created_date', $date])->count();
                }
                
                return $result;
                
            }
            else {
                $_GET['expand'] = "completedTags, totalTags";
                $model = new $this->modelClass;
                $query = $model->find();
                
                try {
                    $provider = new ActiveDataProvider ([
                        'query' => $query,
                        'pagination'=> FALSE,
                    ]);
                
                } catch (Exception $ex) {
                    throw new \yii\web\HttpException(500, 'Internal server error');
                }
                return $provider;
                
            }
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
    }
    
    public function actionTagitems($id) {
        if (!$_POST) {
            
            $post = \Yii::$app->request->post();
            
            $model = new $this->modelClass;
            
            $query = $model->find()
                    ->leftJoin('tag_items_projects item_projects', 'item_projects.project_id=projects.id')->andWhere(["item_projects.item_id" => $id]);
            
            if(isset($post['search'])) {
                foreach($post['search'] as $key => $val)
                    if(isset($val)) {
                        if(in_array($key, $this->partialMatchFields))
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
    
    public function actionGetall() {
        return parent::actionGetall();
    }
    
    public function actionExport() {
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
    
    function getQuarter( $passedDate = '' ) {
        if( $passedDate == '' ) {
            $v = ceil( date( "m" ) / 3 );
            $y = date( "Y" );
        } else {
            $v = ceil( date( "m", $passedDate ) / 3 );
            $y = date( "Y", $passedDate );
        }
        $m = ( $v * 3 ) - 2;
        $date = $y . '-' . $m . '-' . 01;
        $return['begin'] = date( "Y-m-d", strtotime(  $date ) );
        $return['end'] = date( "Y-m-t", strtotime( $return['begin'] . "+ 2 months"  ) );
        $return['version'] = $y . ' - Q' . $v;
        return $return;
    }
    
}
