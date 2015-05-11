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
    
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
        $behaviors['access'] = [
                'class' => \backend\models\RoleAccess::className(),
                'rules' => [
                    [
                        'actions' => ['search', 'get-log', 'logactivity', 'multiple-logactivity', 'index', 'create', 'update', 'view', 'delete'],
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
    
    public function actionGetLog() {
        if (!$_POST) {
            
            $uid = \Yii::$app->request->post("uid");
            
            if(!isset($_GET['expand']))
                $_GET['expand'] = "attachments,user";
            else
                $_GET['expand'] = "attachments,user,".$_GET['expand'];
            
            $model = new $this->modelClass;
            
            $query = $model->find();
            
            $query->joinWith("tag");
            $query->andWhere(['tags.uid' => $uid]);
            
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
    
    public function actionLogactivity() {
        if (!$_POST) {
            
            $post = \Yii::$app->request->post("LogActivity");
            
            $connection = \Yii::$app->db;
            $transaction = $connection->beginTransaction();
            
            $fileManager = new \backend\models\FileManager();
            
            $tagDetails = \backend\models\Tags::findOne(['uid' => $post['uid']]);
            
            $location = "";
            
            if(!isset($post['location'])) {
                $currentLoginDetails = \backend\models\UserTokens::findOne(['token' => $_GET['access-token']]);
                if($currentLoginDetails->login_latlong != "Not available") {
                    $latlong = explode(",", $currentLoginDetails->login_latlong);
                    $location['lat'] = (isset($latlong[0])?$latlong[0]:"");
                    $location['long'] = (isset($latlong[1])?$latlong[1]:"");
                    $location = json_encode($location);
                }
            }
            else
                $location = json_encode ($post['location']);
            
            $model = new $this->modelClass;
            
            $company_id = \yii::$app->user->identity->company_id;
            
            $model->setAttributes($post);
            
            $model->setAttribute("tag_id", $tagDetails->id);
            $model->setAttribute("location", $location);
            
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
    
    public function actionMultipleLogactivity() {
        if (!$_POST) {
            
            $post = \Yii::$app->request->post("LogActivity");
            
            $connection = \Yii::$app->db;
            $transaction = $connection->beginTransaction();
            
            $fileManager = new \backend\models\FileManager();
            
            $fileAttachments = [];
            
            if(isset($post['files']))
              foreach($post['files'] as $file) {
                $filename = str_replace("temp/", "",$file );
                $fileAttachments[] = ['name' => $filename, 'type' => $fileManager->getFileTypeCode("", $filename, "attachments")];
                
                $fileManager->replaceFile("", $filename, "temp/", "attachments");
            }
            
            try {
                $tagIds = $post['tag_id'];
                                
                foreach($tagIds as $tagId) {
                    $model = new $this->modelClass;
                    $post['tag_id'] = $tagId;
                    
                    $model->setAttributes($post);
                    
                    if ($model->save()) {
                        // do something here after saving

                       if(count($fileAttachments))
                        foreach($fileAttachments as $file) {
                            $tagAttachmentModel = new \backend\models\TagActivityAttachment();
                            $tagAttachmentModel->setAttribute("tag_id", $model->tag_id);
                            $tagAttachmentModel->setAttribute("activity_log_id", $model->id);
                            $tagAttachmentModel->setAttribute("filename", $file['name']);
                            $tagAttachmentModel->setAttribute("file_type", $file['type']);

                            if(!$tagAttachmentModel->save()) {
                                $transaction->rollBack();
                                return $tagAttachmentModel;
                            }
                        }
                    }
                    else {
                        $transaction->rollBack();
                        return $model;
                    }
                }
                
                $transaction->commit();
                return "Success";
                
            } catch (Exception $e) {
                $transaction->rollBack();
            }
        } else {
            throw new \yii\web\HttpException(404, 'Invalid Request');
        }
    }
}
