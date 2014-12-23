<?php

namespace backend\models;

use yii\rest\Serializer;

class CustomSerializer extends Serializer {
    
    public $modelClass;
    
    /**
     * Serializes a model object.
     * @param Arrayable $model
     * @return array the array representation of the model
     */
    
    protected function serializeModel($model)
    {
        if ($this->request->getIsHead()) {
            return null;
        } else {
            list ($fields, $expand) = $this->getRequestedFields();
            $result = $model->toArray($fields, $expand);
            
            //$result = array_merge($result, $this->serializeFields($this->getFields()));
            
            return $result;
            
        }
    }
    
    protected function serializeDataProvider($dataProvider)
    {
        $models = $this->serializeModels($dataProvider->getModels());

        if (($pagination = $dataProvider->getPagination()) !== false) {
            $this->addPaginationHeaders($pagination);
        }
                
        if ($this->request->getIsHead()) {
            return null;
        } elseif ($this->collectionEnvelope === null) {
            return $models;
        } else {
            $result = [
                $this->collectionEnvelope => $models,
            ];
            if ($pagination !== false) {
                $result = array_merge($result, $this->serializePagination($pagination));
            } 
            
            unset($result['_links']);
            
            //$result = array_merge($result, $this->serializeFields($this->getFields()));
            
            return $result;
        }
    }
    
    public function getFields() {
                
        $model = \Yii::createObject($this->modelClass);
        
        if($model) {
            
            if($model->fields()) {
                $fields = $model->fields();
                $labels = $model->attributeLabels();
                
                $temp = array();
                                                
                foreach($fields as $key) {
                    if(!isset($labels[$key]))
                        $labels[$key] = $key;
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