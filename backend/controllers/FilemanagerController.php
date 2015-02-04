<?php
namespace backend\controllers;

use yii;
use yii\web\Controller;
use yii\base\Model;
/**
 * Class TagsController
 * @package rest\versions\v1\controllers
 */
class FilemanagerController extends Controller
{
    public $partialMatchFields;
    
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
        
        
	/* the result object that is sent to client*/
        $fileManager->imageResize($filename, 100, 100, $filename);
        return $filename;
    }
    
    public function actionDownload() {
        $type = base64_decode($_GET['type']);
        $filename = $_GET['file'];
                
        $fileManager = new \backend\models\FileManager();
        
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
        
        $fileManager = new \backend\models\FileManager();
        
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