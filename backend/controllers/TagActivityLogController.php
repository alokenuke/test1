<?php
namespace backend\controllers;

use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
/**
 * Class TagsController
 * @package rest\versions\v1\controllers
 */
class TagActivityLogController extends ApiController
{
    public $partialMatchFields;
        
    public function init() {
        $this->modelClass = 'backend\models\TagActivityLog';
        
        $this->partialMatchFields = [''];
        
        parent::init();
    }
    
    public function actionSearch() {
        if (!$_POST) {
            
            $post = \Yii::$app->request->post();
            
            $model = new $this->modelClass;
            
            $query = $model->find();
            
            if(isset($post['select']))
               $query->select($select);

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
            if(isset($post['page']))
                $_GET['page'] = $post['page'];
            
            try {
                $provider = new ActiveDataProvider ([
                    'query' => $query
                ]);
            } catch (Exception $ex) {
                throw new \yii\web\HttpException(500, 'Internal server error');
            }
            return $provider;
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
    }
    
    public function actionLogactivity() {
        if (!$_POST) {
            
            $post = \Yii::$app->request->post("LogActivity");
            
            $connection = \Yii::$app->db;
            $transaction = $connection->beginTransaction();
            
            $fileManager = new \backend\models\FileManager();
            
            $model = new $this->modelClass;
            $company_id = \yii::$app->user->identity->company_id;
            
            $model->setAttributes($post);

            try {
                if ($model->save()) {
                    // do something here after saving
                    
                   if(isset($post['files']))
                    foreach($post['files'] as $file) {
                        $fileName = str_replace("temp/", "",$file );
                        $fileManager->replaceFile("", $fileName, "temp/", "attachments");
                        
                        $tagAttachmentModel = new \backend\models\TagActivityAttachment();
                        $tagAttachmentModel->setAttribute("tag_id", $model->tag_id);
                        $tagAttachmentModel->setAttribute("activity_log_id", $model->id);
                        $tagAttachmentModel->setAttribute("filename", $fileName);
                        $tagAttachmentModel->setAttribute("file_type", $fileManager->getFileTypeCode("", $fileName, "attachments"));
                        
                        if(!$tagAttachmentModel->save()) {
                            $transaction->rollBack();
                            return $tagAttachmentModel;
                        }
                    }
                    
                     $transaction->commit();
                     return "Success";
                }
                else {
                    return $model;
                }

                $transaction->rollBack();
                return $model;
            
            } catch (Exception $e) {
                $transaction->rollBack();
            }
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
    }    
}
