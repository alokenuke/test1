<?php

namespace backend\models;

use yii\rest\Serializer;

use yii\base\Component;
use yii\base\Model;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\web\Link;
use yii\web\Request;
use yii\web\Response;

class CustomSerializer extends Serializer {
    
    public $modelClass;
    public $msg='';
    public $status='SUCCESS';
    public $tokenDetails;
    /**
     * Serializes a model object.
     * @param Arrayab'le $model
     * @return array the array representation of the model
     */
    
    public function serialize($data)
    {
        $identity = json_decode(\Yii::$app->getRequest()->getCookies()->getValue('_identity'));
        
        $token = "";
        if(!isset($_GET['access-token']) && isset($identity[1]))
            $token = $identity[1];
        else
            $token = $_GET['access-token'];
        
        $this->tokenDetails = UserTokens::findOne(['token' => $token]);
        
        if($this->tokenDetails->request_from == 'webapp')
            return parent::serialize ($data);
        
        if ($data instanceof Model && $data->hasErrors()) {
            return $this->serializeModelErrors($data);
        } elseif ($data instanceof Arrayable) {
            return $this->serializeModel($data);
        } elseif ($data instanceof DataProviderInterface) {
            return $this->serializeDataProvider($data);
        } else {
            return array('status'=>$this->status,"items"=>$data,'msg'=>$this->msg );
        }
    }
    
    protected function serializeModel($model)
    {
        if($this->tokenDetails->request_from == 'webapp')
            return parent::serializeModel($model);
        
        if ($this->request->getIsHead()) {
            return null;
        } else {
            list ($fields, $expand) = $this->getRequestedFields();
            $result = $model->toArray($fields, $expand);
            return array('status'=>$this->status,"items"=>$result,'msg'=>$this->msg );
            
        }
    }
    
    /**
     * Serializes the validation errors in a model.
     * @param Model $model
     * @return array the array representation of the errors
     */
    protected function serializeModelErrors($model)
    {
        if($this->tokenDetails->request_from == 'webapp')
            return parent::serializeModelErrors($model);
        
        $this->response->setStatusCode(422, 'Data Validation Failed.');
        $result = [];
        foreach ($model->getFirstErrors() as $name => $message) {
            $result[] = [
                'field' => $name,
                'message' => $message,
            ];
        }

        return array('status'=>"ERROR","items"=>$result,'msg'=>"" );
    }
    
    protected function serializeDataProvider($dataProvider)
    {
        if($this->tokenDetails->request_from == 'webapp')
            return parent::serializeDataProvider($dataProvider);
        
        $models = $this->serializeModels($dataProvider->getModels());

        if (($pagination = $dataProvider->getPagination()) !== false) {
            $this->addPaginationHeaders($pagination);
        }
                
        if ($this->request->getIsHead()) {
            return null;
        } elseif ($this->collectionEnvelope === null) {
            return $models;
        } else {
            if ($pagination !== false) {
                $result = [
                    $this->collectionEnvelope => array_merge($models, $this->serializePagination($pagination)),
                ];
            } 
            else
            {
                $result = [
                    $this->collectionEnvelope => $models,
                ];
            }
            
            
            unset($result['_links']);
            $result = array_merge($result, array('status'=>$this->status,'msg'=>$this->msg ));
            return $result;
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