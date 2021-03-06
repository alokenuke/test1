<?php
namespace backend\models;
use yii\base\Model;
use Yii;

/**
 * Password change form
 */
class FileManager extends Model
{
    public $company, $file_type, $filename, $destination, $pathConstants;
    private $_user;
    
    public function __construct($company="") {
        if($company)
            $this->company = $company;
        else
            $this->company = \yii::$app->user->identity->company_id;
        
        $this->pathConstants = [
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
            "temp" => "temp",
            "browse" => "browse",
            "databackup" => "databackup",
        ];
    }
    
    public function createFolders($type="") {
        if($type)
            return $this->getPath($type);
        else {
            foreach($this->pathConstants as $path) {
                $basePath = \Yii::$app->params['repository'].$this->company;
                
                $pathInfo = explode("/", $path);
                if(count($pathInfo) >0 ) {
                    foreach($pathInfo as $index => $p) {
                        if($index > 0)
                            $p = $pathInfo[0]."/".$p;
                        
                        if(!file_exists($basePath."/".$p))
                            mkdir($basePath."/".$p);
                    }
                }
                else {
                    if(!file_exists($basePath."/".$path))
                        mkdir($basePath."/".$path);
                }
            }
        }
    }
    
    public function getLocalPath($type) {
        return $this->pathConstants[$type];
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
    
    public function getFileTypeCode($path="", $filename, $type) {
        if(!$path)
            $path = $this->getPath($type);
        
        $file = $path."/".$filename;
        
        $fileinfo = new \SplFileInfo($file);
        
        $extension = $fileinfo->getExtension();
        
        $type = "";
        
        if(in_array($extension, ['jpg', 'jpeg', 'png', 'gif']))
            $type = 'image';
        else if(in_array($extension, ['pdf']))
            $type = 'pdf';
        else if(in_array($extension, ['docx', 'doc', 'odt']))
            $type = 'word';
        else if(in_array($extension, ['dwf']))
            $type = 'drawing';
        else if(in_array($extension, ['xls', 'xlsx', 'csv', 'ods']))
            $type = 'excel';
        else if(in_array($extension, ['ppt', 'pptx']))
            $type = 'powerpoint';
        else if(in_array($extension, ['txt', 'rtf']))
            $type = 'text';
        
        return $type;
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
        
        if($oldfile && file_exists($path."/".$oldfile))
            unlink($path."/".$oldfile);
        
        if(file_exists($existingPath."/".$newfile))
            rename($existingPath."/".$newfile, $path."/".$newfile);        
    }
    
    public function getAllowedTypes() {
        return [
            'image/jpeg',
            'image/jpg',
            'application/msword',
            'application/vnd.ms-excel',
            'application/x-msexcel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/x-mspowerpoint',
            'text/plain',
            'application/pdf',
            'image/pjpeg',
            'image/x-dwg',
            'video/mpeg',
            'application/vnd.ms-powerpoint',
            'image/pjpeg',
            'image/x-dwg',
            'video/mpeg',
            'application/msword',
            'application/x-excel',
            'application/x-mathcad',
            'video/x-mpeg',
            'text/xml',
            'image/x-windows-bmp',
            'video/quicktime',
            'x-world/x-3dmf',
            'image/gif',
            'image/png',
            'video/x-msvideo',
        ];
    }
    
    public function getAllowedFileSize($mime) {
        $fileSizes = [
            'image/jpeg' => 1,
            'image/jpg' => 1,
            'application/msword' => 5,
            'application/vnd.ms-excel' => 5,
            'application/x-msexcel' => 5,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 5,
            'application/x-mspowerpoint' => 5,
            'text/plain' => 1,
            'application/pdf' => 5,
            'image/pjpeg' => 1,
            'image/x-dwg' => 5,
            'video/mpeg' => 5,
            'application/msword' => 5,
            'application/vnd.ms-powerpoint' => 5,
            'image/pjpeg' => 1,
            'application/x-mathcad' => 5,
            'text/xml' => 1,
            'image/x-windows-bmp' => 1,
            'video/quicktime' => 5,
            'x-world/x-3dmf' => 5,
            'image/gif' => 1,
            'image/png' => 1,
            'video/x-msvideo' => 5,
        ];
        return $fileSizes[$mime];
    }
    
    public function removeDirectory($dir) {
        $dir = \Yii::$app->params['repository'].$dir;
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }

        rmdir($dir);
    }
}