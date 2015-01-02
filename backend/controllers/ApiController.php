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
        
    public function init() {
        $this->modelClass = 'common\models\\'.  ucfirst($_REQUEST['mod']);
        
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
        if(isset($this->identity[1]))
            return [
                'token' => $this->identity[1]
            ];
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
            
            $model = new $this->modelClass;
            $query = $model->find();
            
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
        
        $this->modelClass = 'common\models\\'.  ucfirst($_REQUEST['mod']);
        
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
}
