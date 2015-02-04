<?php
namespace backend\controllers;

use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;

/**
 * Class ApiController
 * @package rest\versions\v1\controllers
 */
class ApiController extends ActiveController
{
    public $serializer;
    public $identity;
    public $modelClass;
    
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'index' => [
                'class' => 'yii\rest\IndexAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'view' => [
                'class' => 'yii\rest\ViewAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'create' => [
                'class' => 'yii\rest\CreateAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'scenario' => $this->createScenario,
            ],
            'update' => [
                'class' => 'yii\rest\UpdateAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'scenario' => $this->updateScenario,
            ],
            'delete' => [
                'class' => 'backend\models\DeleteAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'options' => [
                'class' => 'yii\rest\OptionsAction',
            ],
        ];
    }
        
    public function init() {
        
        $this->identity = json_decode(\Yii::$app->getRequest()->getCookies()->getValue('_identity'));
                
        if(!isset($_GET['access-token']) && isset($this->identity[1]))
            $_GET['access-token'] = $this->identity[1];
        
        $this->serializer = [
            'class' => 'backend\models\CustomSerializer',
            'collectionEnvelope' => 'items',
            'modelClass' => $this->modelClass,
        ];
    }
    
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => QueryParamAuth::className(),
        ];
        return $behaviors;
    }
    
    public function actionGettoken() {
        $this->modelClass = "backend\models\User";
        if(isset($this->identity[1]))
            return [
                'token' => $this->identity[1]
            ];
    }

    public function actionGenCode() {
        
        $text = $_GET['text']; 
        $type = $_GET['type'];
        if($_GET) {           
            
            error_reporting(0);
         
            if($type=='2') {
                $qrCode= new \backend\models\BarCodeGenerator\DNS2DBarcode();
                $qrCode->save_path= "userUploads/".\yii::$app->user->identity->company_id . "/tagsImages/qrCode/";
                echo "<img src='/".$qrCode->getBarcodePNGPath($text, 'qrcode',10, 10)."'>";
                die;
            }
            else {
                $qrCode= new \backend\models\BarCodeGenerator\DNS1DBarcode();
                $qrCode->save_path= "userUploads/".\yii::$app->user->identity->company_id . "/tagsImages/barCode/";
                echo "<img src='/".$qrCode->getBarcodePNGPath($text, 'PHARMA', 5, 100)."'>";
                
//                echo $qrCode->getBarcodePNGPath($text, 'C128');
//                echo "<br />";
//                echo $qrCode->getBarcodePNGPath($text, 'C128A');
//                echo "<br />";
//                echo $qrCode->getBarcodePNGPath($text, 'C128B');
//                echo "<br />";
////                
//                echo $qrCode->getBarcodePNGPath($text, 'CODE11');
////                echo "<br />";
//                echo $qrCode->getBarcodePNGPath($text, 'PHARMA');
//                echo "<br />";
//                echo $qrCode->getBarcodePNGPath($text, 'PHARMA2T');
//                echo "<br />";
                die;
            }
        }
    }
    
    public function actionGetdata() {
        if (!$_POST) {
            
            $post = \Yii::$app->request->post();
            
            if(isset($post['sort']))
                $_GET['sort'] = $post['sort'];
            if(isset($post['page']))
                $_GET['page'] = $post['page'];
            
            $model = new $this->modelClass;
            
            try {
                $provider = new ActiveDataProvider ([
                    'query' => $model->find()
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
        if (!empty($_GET)) {
            
            $post = \Yii::$app->request->post();
            
            $model = new $this->modelClass;
            
            $query = $model->find();
            
            if(isset($post['search'])) {
                foreach($post['search'] as $key => $val)
                    if(isset($val)) {
                        if(in_array($key, $this->partialMatchFields))
                            $query->andWhere(['like', $key, $val]);
                        else
                            $query->andWhere([$key => $val]);
                    }
            }
            
            if(isset($post['sort']))
                $_GET['sort'] = $post['sort'];
             
            try {
                $provider = new ActiveDataProvider ([
                    'query' => $query,
                    'pagination' => false
                ]);
            } catch (Exception $ex) {
                throw new \yii\web\HttpException(500, 'Internal server error');
            }
            return $provider;
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
    }
    
    public function actionFields() {
        
        return $this->serializeFields($this->getFields());
    }
        
    public function getFields() {
                
        $model = \Yii::createObject($this->modelClass);
        
        $create = true;
        
        if(!isset($_REQUEST['create']))
            $create = true;
        else
            $create = $_REQUEST['create'];
        
        if($model) {
            if(method_exists($model, "getFormFields")) {
                $fields = $model->getFormFields($create);
                $labels = $model->attributeLabels();
                
                $temp = array();
                
                foreach($fields as $key) {
                    $temp[] = array('key' => $key, 'label' => $labels[$key]);
                }
                
                return $temp;                
                
            }
            else {
                $fields = $model->getAttributes();
                $labels = $model->attributeLabels();
                
                $temp = array();
                
                foreach($fields as $key => $field) {
                    $temp[] = array('key' => $key, 'label' => $labels[$key]);
                }
                
                return $temp;
                
            }
        }
    }
    
    /**
     * Serializes fields into an array.
     * @param Fields $fields
     * @return array the array representation of the fields
     */
    protected function serializeFields($fields)
    {
        return [
            '_fields' => $fields
        ];
    }
    
    public function in_array_r($key, $needle, $haystack, $strict=false) {
        foreach ($haystack as $item) {
            if (($strict ? $item[$key] === $needle : $item[$key] == $needle) || (is_array($item[$key]) && $this->in_array_r($needle, $item[$key], $strict))) {
                return true;
            }
        }
        
        return false;
    }
}
