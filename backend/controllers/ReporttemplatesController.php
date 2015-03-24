<?php

namespace backend\controllers;

use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
/**
 * Class TagsController
 * @package rest\versions\v1\controllers
 */
class ReporttemplatesController extends ApiController
{
    public function init() {
        $this->modelClass = 'backend\models\ReportTemplates';
        
        parent::init();
    }
    
    public function actionSearch() {
        try {
            $model = new $this->modelClass;
            $provider = new ActiveDataProvider ([
                'query' => $model->find(),
                'pagination'=> false,                        
            ]);
        } catch (Exception $ex) {
            throw new \yii\web\HttpException(500, 'Internal server error');
        }
        return $provider;
    }
    
    public function actionGetall() {
        return parent::actionGetall();
    }
    
}
