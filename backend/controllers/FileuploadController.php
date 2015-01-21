<?php
namespace backend\controllers;

use yii;
use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
use yii\base\Model;
use yii\bootstrap\ActiveForm;
/**
 * Class TagsController
 * @package rest\versions\v1\controllers
 */
class FileuploadController extends ApiController
{
    public $partialMatchFields;
        
    //public $documentPath = 'userUploads/';
    public $documentPath = 'temp/';

    public function verbs()
    {
        $verbs = parent::verbs();
        $verbs[ "upload" ] = ['POST' ];
        return $verbs;
    }
    
    public function actionUpload()
    {
        
        $postdata = fopen( $_FILES[ 'file' ][ 'tmp_name' ], "r" );
        /* Get file extension */
        $extension = substr( $_FILES[ 'file' ][ 'name' ], strrpos( $_FILES[ 'file' ][ 'name' ], '.' ) );

        /* Generate unique name */
        $filename = $this->documentPath.\yii::$app->user->identity->company_id."/userImages/" . uniqid() . $extension;

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
        $this->resize($filename, 100, 100, $filename);
        return $filename;
    }
    
    function resize($imagePath, $destinationWidth, $destinationHeight, $destinationPath)
    {
        if (file_exists($imagePath)) {
            $imageInfo = getimagesize($imagePath);
            $sourceWidth = $imageInfo[0];
            $sourceHeight = $imageInfo[1];
            $source_aspect_ratio = $sourceWidth / $sourceHeight;
            $thumbnail_aspect_ratio = $destinationWidth / $destinationHeight;
            if ($sourceWidth <= $destinationWidth && $sourceHeight <= $destinationHeight) {
                $thumbnail_image_width = $sourceWidth;
                $thumbnail_image_height = $sourceHeight;
            } elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
                $thumbnail_image_width = (int) ($destinationHeight * $source_aspect_ratio);
                $thumbnail_image_height = $destinationHeight;
            } else {
                $thumbnail_image_width = $destinationWidth;
                $thumbnail_image_height = (int) ($destinationWidth / $source_aspect_ratio);
            }
            $destinationWidth = $thumbnail_image_width;
            $destinationHeight = $thumbnail_image_height;
            $mimeType = $imageInfo['mime'];
            $destinationWidth = $thumbnail_image_width;
            $destinationHeight = $thumbnail_image_height;
            $destination = imagecreatetruecolor($destinationWidth, $destinationHeight);
            if ($mimeType == 'image/jpeg' || $mimeType == 'image/pjpeg') {
                $source = imagecreatefromjpeg($imagePath);
                imagecopyresampled($destination, $source, 0, 0, 0, 0, $destinationWidth, $destinationHeight, $sourceWidth, $sourceHeight);
                $destinationPath = $destinationPath;
                imagejpeg($destination, $destinationPath);
            } else if ($mimeType == 'image/gif') {
                $source = imagecreatefromgif($imagePath);
                imagecopyresampled($destination, $source, 0, 0, 0, 0, $destinationWidth, $destinationHeight, $sourceWidth, $sourceHeight);
                $destinationPath = $destinationPath;
                imagegif($destination, $destinationPath);
            } else if ($mimeType == 'image/png' || $mimeType == 'image/x-png') {
                $source = imagecreatefrompng($imagePath);
                imagecopyresampled($destination, $source, 0, 0, 0, 0, $destinationWidth, $destinationHeight, $sourceWidth, $sourceHeight);
                $destinationPath = $destinationPath;
                imagepng($destination, $destinationPath);
            } else {
                //echo 'This image type is not supported.';
                return false;
            }
        } else {
            //echo 'The requested file does not exist.';
            return true; 
        }
    }
 }   