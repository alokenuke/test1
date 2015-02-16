<?php
namespace backend\models;
use yii\base\Model;
use Yii;

/**
 * Password change form
 */
class FileManager extends Model
{
    public $company, $file_type, $filename, $destination;
    private $_user;
    
    public function __construct() {
        $this->company = \yii::$app->user->identity->company_id;
    }
    
    public function getLocalPath($type) {
        $pathConstants = [
            "" => "",
            "project_image" => "projectImage",
            "barcode" => "tagsImages/barCode",
            "nfccode" => "tagsImages/NFC",
            "qrcode" => "tagsImages/qrCode",
            "Attendancebarcode" => "attendanceTagImages/barCode",
            "Attendancenfccode" => "attendanceTagImages/NFC",
            "Attendanceqrcode" => "attendanceTagImages/qrCode",
            "user_image" => "userImages",
            "attachments" => "attachments",
        ];
        return $pathConstants[$type];
    }
    
    public function getRootPath() {
        return \Yii::$app->params['repository'].$this->company;
    }

    /**
     * Get path.
     *
     * @return string of path.
     */
    public function getPath($type) {
        $localPath = $this->getLocalPath($type);
        $path = \Yii::$app->params['repository'].$this->company;
        
        if($localPath)
            $path = $path."/".$localPath;
        
        if(!file_exists($path))
            mkdir($path);
        return $path;
    }
    
    public function getFileType($path="", $filename, $type) {
        if(!$path)
            $path = $this->getPath($type);
        
        $file = $path."/".$filename;
        
        $fileinfo = new \SplFileInfo($file);
        $extension = $fileinfo->getExtension();
        
        return $extension;
    }
    
    public function imageResize($imagePath, $destinationWidth, $destinationHeight, $destinationPath) {
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
    
    public function replaceFile($oldfile, $newfile, $existingPath, $type) {
        $path = $this->getPath($type);
        
        if(file_exists($path."/".$oldfile))
            unlink($path."/".$oldfile);
        
        rename($existingPath."/".$newfile, $path."/".$newfile);        
    }
}