<?php
namespace backend\controllers;

use mPDF;
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
                        'actions' => ['search', 'get-log', 'get-log-pdf', 'logactivity', 'multiple-logactivity', 'index', 'create', 'update', 'view', 'delete'],
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
    
    /** Get the Tag logs as PDF **/
    public function actionGetLogPdf() {
      
      $dataProvider = $this->actionGetLog();
      
      $tagModel = \backend\models\Tags::find()->where(['tags.uid' => \Yii::$app->request->post("uid")])->one();
      $projectName = $tagModel->fields()['project_name'];
      $projectName = $projectName();
      
      $models = $dataProvider->getModels();
      
      if(!$models)
        throw new \yii\web\HttpException(404, 'No data for tag');
      
      //generate the PDF
        $pdf = new mPDF('', array(216, 279));
        
        $pdf->SetAutoPageBreak(TRUE);
        $pdf->HREF = '';
        $pdf->SetDefaultFont('Arial', 'B', 8);
        $pdf->SetDefaultFontSize(8);
        $pdf->SetLeftMargin(5);
        $pdf->SetRightMargin(5);
        $pdf->SetTopMargin(5);
        $pdf->DeflMargin = 5;
        $pdf->DefrMargin = 5;
        $pdf->setAutoTopMargin = $pdf->setAutoBottomMargin = false;      
        
        $content = '<div style="padding-top: 10px; padding-bottom: 10px;"><h2>
                            '. $tagModel->type .' | '. $tagModel->uid .' | '. $tagModel->tag_name .' | '. $tagModel->tag_item_id .' | 
                            '. $projectName .'
                         </h2><table>';
        
        $fileManager = new \backend\models\FileManager();
        
        $index = 1;
        
        foreach($models as $model) {
          
          $attachments = [];
          $attachmentsBlock = '';
          $stageInfo = '';
          $answer = '';
          $includeTr = $index % 2;
          
          foreach($model->attachments as $attachment) {

            $attachments[] = $attachment['filename'];
            
            //$pdf->{$attachment['id']} = file_get_contents($basePath.'/repository/'.\Yii::$app->user->identity->company_id.'/attachments/'.$attachment['filename']);
 
            $img = $fileManager->getPath('attachments').'/'.$attachment['filename'];
            list(, $ext) = $attachment['filename'];
            
            if(!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp']) && !file_exists($img))
              continue;
            
            $attachmentsBlock .= '<span style="margin-rigth: 5px;"> <img width="20mm" height="20mm" src="'. $img .'"></img></span>';
            //$attachmentsBlock .= '<span style="margin-rigth: 5px;"> <img src="var:'. $attachment['id'] .'"></img></span>';
          }          
          
          if($model->stageInfo)
            $stageInfo = $model->stageInfo->process_name;
          
          $answer = $model->fields()['answer'];
          $answer = $answer();
          if(is_object($answer))
            $answer = $answer->process_name;
          
          if($index == 1)
            $content .= '<tr>';
          else if($includeTr)
            $content .= '</tr><tr>';
          
          $content .= '<td style="border: 1px solid grey; margin-top:5px; padding: 5px; width: 50%;">
                        
                        <div>
                          <p><label>Process Level:</label> '. $stageInfo .'</p>
                          <p><label>Comment:</label> '. $model->comment .'</p>
                          <p><label>Date Time:</label> '. $model->logged_date .'</p>
                          <p><label>Update By:</label> '. $model->loggedBy->first_name.' '.$model->loggedBy->last_name .'</p>
                        </div>
                        
                        <div>
                          '. $attachmentsBlock .'
                        </div>
                      </td>';
          
          $index++;
        }
        $content .= '</tr>';
        $content .= '</table></div>';
        
        $pdf->WriteHTML($content);
        
        $file_download = "temp/tag_history_".date("Ymd_His").".pdf";
        $pdf->Output($file_download, 'f');
        
        return $file_download;        
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
