<?php

namespace backend\controllers;

use Yii;
use mPDF;
use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;

class ReportsdownloadController extends ApiController
{
    
    public $enableCsrfValidation = false;
    
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
                        'actions' => ['previewtemplate', 'printlabel', 'printtimeattendancelabel', 'print-tag-report', 'print-tag-reports'],
                        'allow' => true,
                        'roles' => ['Client'],
                    ],
                ]
        ];
        
        return $behaviors;
    }
        
    public function actionPreviewtemplate()
    {
        $template_param = \Yii::$app->request->post();
        $checkedLabels = $template_param['checked_labels'];
     
        //-----------------------------Set hardcoded values if not set---------------------------------------------------------------
        $data['leftMargin'] = isset($template_param['left_margin']) ? (int) $template_param['left_margin'] : 5;
        $data['topMargin'] = isset($template_param['top_margin']) ? (int) $template_param['top_margin'] : 5;
        $data['rightMargin'] = isset($template_param['right_margin']) ? (int) $template_param['right_margin'] : 5;
        $data['bottomMargin'] = isset($template_param['bottom_margin']) ? (int) $template_param['bottom_margin'] : 5;

        $data['numberHorizontal'] = isset($template_param['num_label_horizontal']) ? (int) $template_param['num_label_horizontal'] : 3;
        $data['numberVertical'] = isset($template_param['num_label_vertical']) ? (int) $template_param['num_label_vertical'] : 4;
        $data['hSpace'] = isset($template_param['hor_label_spacing']) ? (int) $template_param['hor_label_spacing'] : 4;
        $data['vSpace'] = isset($template_param['ver_label_spacing']) ? (int) $template_param['ver_label_spacing'] : 5;
        $data['pageWidth'] = isset($template_param['page_width']) ? (int) $template_param['page_width'] : 216;
        $data['pageHeight'] = isset($template_param['page_height']) ? (int) $template_param['page_height'] : 279;
        $data['logo_width'] = isset($template_param['logo_width']) ? $template_param['logo_width'] : 15;
        $data['logo_height'] = isset($template_param['logo_height']) ? $template_param['logo_height'] : 15;
        $data['type'] = isset($template_param['print_type']) ? $template_param['print_type'] : 'qr';
        $data['font_size'] = (isset($template_param['font_size']) ? (int) $template_param['font_size'] : 5) * 0.75;
        $data['logo_height'] = isset($template_param['logo_height']) ? (int) $template_param['logo_height'] : 5;
        
        $data['logo_position'] = isset($template_param['logo_position']) ? $template_param['logo_position'] : 'topLeft';
        $data['additional_notes'] = isset($template_param['additional_notes']) ? $template_param['additional_notes'] : "";
        
        if (!empty($template_param['logo']))
            $data['logo'] = isset($template_param['logo']) ? $template_param['logo'] : '';

        elseif (!empty($template_param['old_logo_file']))
            $data['logo'] = isset($template_param['old_logo_file']) ? $template_param['old_logo_file'] : '';
        
        $pdf = new mPDF('', array($data['pageWidth'], $data['pageHeight']));
        
        $pdf->SetAutoPageBreak(false);
        $pdf->HREF = '';
        $pdf->SetDefaultFont('Arial', 'B', 8);
        $pdf->SetDefaultFontSize(8);
        $pdf->SetLeftMargin($data['leftMargin']);
        $pdf->SetRightMargin($data['rightMargin']);
        $pdf->SetTopMargin($data['topMargin']);
        $pdf->DeflMargin = $data['leftMargin'];
        $pdf->DefrMargin = $data['leftMargin'];
        $pdf->setAutoTopMargin = $pdf->setAutoBottomMargin = false;
        
        $labelW = $this->calculatedLabelW($data);
        $labelH = $this->calculatedLabelH($data);
        
        $codeImage = "";
        if($data['type']=='qr')
            $codeImage = 'images/qr-code.png';
        else if($data['type']=='bar')
            $codeImage = 'images/bar-code.png';
        else if($data['type']=='nfc' && isset($data['logo']) && file_exists($data['logo']))
            $codeImage = $data['logo'];
        else if($data['type']=='nfc' && isset($data['logo'])) {
            $logoFilename = array_pop(preg_split("/((=)|(\/))/", $data['logo']));
            $fileManager = new \backend\models\FileManager();
            $codeImage = $fileManager->getPath("")."/".$logoFilename;
        }
        else
            $codeImage = 'images/nfc.png';

        $imageUrl = "images/logo.png";
        $labelInfo = "";
        
        foreach($checkedLabels as $label) {
            if($label['isChecked']) {
                $tempLabel = "";
                if($label['showLabel'])
                    $tempLabel = '<strong>'.$label['label'].'</strong> : ';
                
                if($label['name'] == 'tag_type')
                    $labelInfo .= '<span>'.$tempLabel.'mT</span>';
                else if($label['name'] == 'uid')
                    $labelInfo .= '<span>'.$tempLabel.'4SOMQ95506</span>';
                else
                    $labelInfo .= '<span>'.$tempLabel.'Test '.$label['label'].'</span>';
                if($label['lineBreak'])
                    $labelInfo .= '<br />';
                else
                    $labelInfo .= ' <strong>|</strong> ';
            }
        }
        
        if($data['additional_notes'])
            $labelInfo .= '<div><strong>Note : '.$data['additional_notes'].'</strong></div>';
        
        $infoBox = "<div style='margin-left: 2mm;float:left;min-width: 40mm;font-size: ".$data['font_size']."px;'>".$labelInfo."</div>";
        
        $logoStyle = "";
        
        if($data['logo_position']=='topLeft') {
            $logoStyle = "float:left;width: ".$data['logo_width']."mm;";
        }
        else if($data['logo_position']=='topRight') {
            $logoStyle = "float:right;width: ".$data['logo_width']."mm;";
        }
        else if($data['logo_position']=='topMiddle') {
            $logoStyle = "width:100%;";
        }
        else if($data['logo_position']=='bottomLeft') {
            $logoStyle = "clear:left;float:left;vertical-align:bottom;width: ".$data['logo_width']."mm;";
        }
        else if($data['logo_position']=='bottomRight') {
            $logoStyle = "clear:left;float:right;vertical-align:bottom;width: ".$data['logo_width']."mm;";
        }
        else if($data['logo_position']=='bottomMiddle') {
            $logoStyle = "clear:left;width: 100%;vertical-align:bottom;";
        }
        else if($data['logo_position']=='leftMiddle') {
            $imageInfo = getimagesize($codeImage);
            $height = $data['logo_width']*$imageInfo[1]/$imageInfo[0];
            $logoStyle = "float:left;padding-top:".(($labelH - $height-7 )/2)."mm;width: ".$data['logo_width']."mm;";
        }
        else if($data['logo_position']=='rightMiddle') {
            $height = $data['logo_width']*$imageInfo[1]/$imageInfo[0];
            $logoStyle = "float:right;padding-top:".(($labelH - $height-7 )/2)."mm;width: ".$data['logo_width']."mm;";
        }
        
        $logoBox = "<div id='logoContainer' style='".$logoStyle."text-align:center;max-height: 10mm;overflow;hidden;position:absolute;'>UID: 4SOMQ95506<br /><img src='".$codeImage."' style='width: ".$data['logo_width']."mm;height: ".$data['logo_height']."mm;' /><br />http://sitetrack-nfc.com</div>";
        
        $labelHtml = "";
        
        if($data['logo_position']=='bottomLeft' || $data['logo_position']=='bottomRight' || $data['logo_position']=='bottomMiddle') {
            $labelHtml = $infoBox.$logoBox;
        }
        else {
            $labelHtml = $logoBox.$infoBox;
        }
        
        $content = "<div style='margin-left: 0;width: 100%;'>";

        for ($vindex = 0; $vindex < $data['numberVertical']; $vindex++) {
            $marginTop = 0;
                if($vindex)
                    $marginTop = $data['vSpace'] / 2;
            $content .= "<div class='testClass' style='margin-top: ".$marginTop."mm;clear:both;'>";
            
            for ($hindex = 0; $hindex < $data['numberHorizontal']; $hindex++) {
                $marginLeft = 0;
                if($hindex)
                    $marginLeft = $data['hSpace'] / 2;
                $content .= "<div style='margin-left: ".$marginLeft."mm;padding: 5px;border: 1px solid #ccc;float:left;width: ".($labelW-3)."mm;background: url(images/labelLogo.png) no-repeat right bottom;padding: 5px;'>".$labelHtml."</div>";
            }
            $content .= "</div>";
        }
        $content .= "<div>";
                
        $pdf->WriteHTML($content);
        
        $file_download = "temp/preview_template_".date("Ymd_His").".pdf";
        $pdf->Output($file_download, 'f');
        return $file_download;
    }
       
}