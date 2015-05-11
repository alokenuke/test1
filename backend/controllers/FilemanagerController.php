<?php
namespace backend\controllers;

use yii;
use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
use yii\base\Model;
/**
 * Class TagsController
 * @package rest\versions\v1\controllers
 */
class FilemanagerController extends ApiController
{
    
    public function init() {
        parent::init();
    }
    
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
        $behaviors['access'] = [
                'class' => \backend\models\RoleAccess::className(),
                'rules' => [
                    [
                        'actions' => ['upload', 'uploadimage', 'uploaddoc', 'download', 'getimage'],
                        'allow' => true,
                        'roles' => ['Client', 'Site'],
                    ]
                ]
        ];
        
        return $behaviors;
    }
    
    public $documentPath = 'temp/';

    public function verbs()
    {
        $verbs = parent::verbs();
        $verbs[ "upload" ] = ['POST' ];
        return $verbs;
    }
    
    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }
    
    public function actionUpload() {
        $fileManager = new \backend\models\FileManager();
        
        $mimeType = $_FILES['file']['type'];
        
        if(!in_array($mimeType, $fileManager->getAllowedTypes())) {
            Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed.');
            return "You have uploaded an unsupported file of type ".$mimeType.". Please try again.";
        }
        else if(($fileManager->getAllowedFileSize($mimeType) * 1024 * 1024) < $_FILES['file']['size']) {
            Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed.');
            return "Max file size is ".$fileManager->getAllowedFileSize($mimeType)."MB. Please try again.";
        }
        
        $postdata = fopen( $_FILES[ 'file' ][ 'tmp_name' ], "r" );
        
        /* Get file extension */
        $extension = substr( $_FILES[ 'file' ][ 'name' ], strrpos( $_FILES[ 'file' ][ 'name' ], '.' ) );

        /* Generate unique name */
        $filename = $this->documentPath . uniqid() . $extension;

        /* Open a file for writing */
        $fp = fopen( $filename, "w" );

        /* Read the data 1 KB at a time
          and write to the file */
        while( $data = fread( $postdata, 1024 ) )
            fwrite( $fp, $data );

        /* Close the streams */
        fclose( $fp );
        fclose( $postdata );
        
        if(in_array($mimeType, ['image/jpeg', 'image/pjpeg', 'image/gif', 'image/png', 'image/x-png'])) {
            /* the result object that is sent to client*/
            $fileManager->imageResize($filename, 100, 100, $filename);
        }
        return $filename;
    }
    
    public function actionUploadimage() {
        
        $fileManager = new \backend\models\FileManager();
        
        $uploadedFile = $_FILES['upload'];
        
        $mimeType = $_FILES['upload']['type'];
        
        if(!in_array($mimeType, $fileManager->getAllowedTypes())) {
            Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed.');
            return "You have uploaded a unsupported file of type ".$mimeType.". Please try again.";
        }
        else if(($fileManager->getAllowedFileSize($mimeType) * 1024 * 1024) < $_FILES['upload']['size']) {
            Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed.');
            return "Max file size is ".$fileManager->getAllowedFileSize($mimeType)."MB. Please try again.";
        }
        
        $type = str_replace("image/", "", $uploadedFile['type']);
        
        if(in_array($type, ['jpeg', 'jpg', 'gif', 'png'])) {
            
            if($type=='jpeg')
                $type = 'jpg';
            
            $fileManager = new \backend\models\FileManager();
            
            $filename = uniqid().".".$type;
            
            $filePath = $fileManager->getPath("browse"). "/" . $filename;
            
            move_uploaded_file($uploadedFile['tmp_name'], $filePath);
            
            $imageUrl = "/filemanager/getimage?type=".  base64_encode("browse")."&file=".$filename;
            
            // Required: anonymous function reference number as explained above.
            $funcNum = $_GET['CKEditorFuncNum'] ;
            // Optional: instance name (might be used to load a specific configuration file or anything else).
            $CKEditor = $_GET['CKEditor'] ;
            // Optional: might be used to provide localized messages.
            $langCode = $_GET['langCode'] ;

            // Usually you will only assign something here if the file could not be uploaded.
            $message = '';

            echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '$imageUrl', '$message');</script>";
            
            return ;
        }
        else {
            Yii::$app->getResponse()->setStatusCode(422, 'Invalid image format.');
            return 'error';
        }
    }
    
	//upload document
    
    public function actionUploaddoc() {
        
        $extension = end(explode('.', $_FILES['file']['name']));
        
        $fileManager = new \backend\models\FileManager();
        
        $mimeType = $_FILES['file']['type'];
        
        if(!in_array($mimeType, $fileManager->getAllowedTypes())) {
            Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed.');
            return "You have uploaded an unsupported file of type ".$mimeType.". Please try again.";
        }
        else if(($fileManager->getAllowedFileSize($mimeType) * 1024 * 1024) < $_FILES['file']['size']) {
            Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed.');
            return "Max file size is ".$fileManager->getAllowedFileSize($mimeType)."MB. Please try again.";
        }
        
        if(in_array($extension, ['csv', 'xls', 'xlsx'])) {
            
            $fileManager = new \backend\models\FileManager();
            
            $filename = uniqid().".".$_FILES['file']['name'];
            
            $filePath = 'temp'. "/" . $filename;
            
            move_uploaded_file($_FILES['file']['tmp_name'], $filePath);
            
            return $filePath;
        }
        else {
            Yii::$app->getResponse()->setStatusCode(422, 'Invalid image format.');
            return 'error';
        }
    }    
    
    public function actionDownload() {
        $type = base64_decode($_GET['type']);
        $filename = $_GET['file'];
                
        $company = "";
        
        if(isset($_GET['company']) && isset($_GET['company']) > 0)
            $company = $_GET['company'];
        
        $fileManager = new \backend\models\FileManager($company);
        
        $filePath = $fileManager->getPath($type);
        
        $fileInfo = $fileManager->getFileType($filePath, $filename, $type);
        
        if($fileInfo) {
            try {
                return \Yii::$app->getResponse()->sendFile($filePath."/".$filename, $filename);
            } catch (Exception $ex) {
                throw new \yii\web\HttpException(500, 'Internal Error');
            }
        }
        else {
            throw new \yii\web\HttpException(404, 'File not available');
        }
    }
    
    public function actionGetimage() {
        $type = base64_decode($_GET['type']);
        $filename = $_GET['file'];
        
        $company = "";
        
        if(isset($_GET['company']) && isset($_GET['company']) > 0)
            $company = $_GET['company'];
        
        $fileManager = new \backend\models\FileManager($company);
        
        $filePath = $fileManager->getPath($type);
        
        $fileInfo = $fileManager->getFileType($filePath, $filename, $type);
        
        if($fileInfo[0]) {
            try {
                return \Yii::$app->getResponse()->sendFile($filePath."/".$filename, $filename, ['inline' => 'inline']);
            } catch (Exception $ex) {
                throw new \yii\web\HttpException(500, 'Internal Error');
            }
        }
        else {
            throw new \yii\web\HttpException(404, 'File not available');
        }
    }
    
 }   