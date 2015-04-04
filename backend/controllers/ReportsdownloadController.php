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
    
    public function actionPrintlabel()
    {
        $uids = \Yii::$app->request->post("uid");
        $label_tempalte = \Yii::$app->request->post("label_template");
        
        $labelTemplate = \backend\models\LabelTemplates::findOne(['id' => $label_tempalte]);
        
        if(!isset($_GET['expand']))
            $_GET['expand'] = "project_level,itemObj,processObj,project";
        else
            $_GET['expand'] = "project_level,itemObj,processObj,project,".$_GET['expand'];
        
        if(count($uids) && $labelTemplate) {
            
            $template_param = \yii\helpers\ArrayHelper::toArray($labelTemplate);
            
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
            
            $fileManager = new \backend\models\FileManager();
            
            $nfcLogo = 'images/nfc.png';

            $imageUrl = "images/logo.png";

            $labels = array_values($uids);

            if (isset($labels)) {
                
                $index = 0;
                $content = "";
                $imageUrl = "images/logo.png";
                $pdf = new mPDF('', array($data['pageWidth'], $data['pageHeight']));
        
                $pdf->SetAutoPageBreak(true);
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
                
                while (1) {
                    //-----------------------------Print Label-----------------------------------------------------------------
                    //$pdf->AddPage();
                    
                    $content .= "<div style='width: 100%;'>";

                    for ($vindex = 0; $vindex < $data['numberVertical']; $vindex++) {
                        $marginTop = 0;
                        if($vindex)
                            $marginTop = $data['vSpace'] / 2;
                        
                        $content .= "<div class='testClass' style='margin-top: ".$marginTop."mm;clear:both;'>";
                        
                        for ($hindex = 0; $hindex < $data['numberHorizontal']; $hindex++) {
                            
                            $marginLeft = 0;
                            if($hindex)
                                $marginLeft = $data['hSpace'] / 2;
                            
                            $tagsQuery = \backend\models\Tags::find()->andWhere(['uid' => $labels[$index]])->one();
                            
                            $serializer = new \backend\models\CustomSerializer();

                            $tagDetails = $serializer->serialize($tagsQuery);
                            
                            if(!count($tagDetails)) {
                                $index++;
                                continue;
                            }
                            
                            $codeImage = "";
                            
                            if($data['type']=='qr' || $data['type']=='bar') {
                                $codeImage = $fileManager->getPath($data['type']."code")."/".$tagDetails['uid'].".png";
                                
                                if(!file_exists($codeImage))
                                    $codeImage = "images/noimage.png";
                            }
                            else if($data['type']=='nfc' && isset($data['logo']) && file_exists($data['logo']))
                                $codeImage = $data['logo'];
                            else if($data['type']=='nfc' && isset($data['logo'])) {
                                $logoFilename = array_pop(preg_split("/((=)|(\/))/", $data['logo']));
                                $fileManager = new \backend\models\FileManager();
                                $codeImage = $fileManager->getPath("")."/".$logoFilename;
                            }
                            else
                                $codeImage = 'images/nfc.png';
                            
                            $labelInfo = "";
                            
                            foreach($template_param['checked_labels'] as $label) {
                                if($label['isChecked']) {
                                    $tempLabel = "";
                                    if($label['showLabel'])
                                        $tempLabel = '<strong>'.$label['label'].'</strong> : ';
                                    
                                    $value = "";
                                    switch ($label['name']) {
                                        case 'tag_type': 
                                            $value = $tagDetails['type'];
                                            break;
                                        case 'company_name': 
                                            $value = $tagDetails['project']['company_name'];
                                            break;
                                        case 'project_name': 
                                            $value = $tagDetails['project']['project_name'];
                                            break;                                        
                                        case 'client_name': 
                                            $value = $tagDetails['project']['client_name'];
                                            break;
                                        case 'project_address': 
                                            $value = $tagDetails['project']['project_address']." ".$tagDetails['project']['project_city']." ".$tagDetails['project']['project_country'];
                                            break;
                                        case 'client_location': 
                                            $value = $tagDetails['project']['client_location'];
                                            break;
                                        case 'project_location': 
                                            $value = $tagDetails['project']['project_location'];
                                            break;
                                        case 'main_contractor': 
                                            $value = $tagDetails['project']['main_contractor'];
                                            break;
                                        case 'tag_item': 
                                            $temp = [];
                                            foreach($tagDetails['itemObj'] as $obj)
                                            {
                                                $temp[] = $obj['item_name'];
                                            }
                                            $value = implode(" > ", $temp);
                                            break;
                                        case 'process': 
                                            $temp = [];
                                            foreach($tagDetails['processObj'] as $obj)
                                            {
                                                $temp[] = $obj['process_name'];
                                            }
                                            $value = implode(" > ", $temp);
                                            break;
                                        case 'project_level': 
                                            $value = implode(" > ", $tagDetails['project_level']);
                                            break;
                                        default: 
                                            $value .= $tagDetails[$label['name']];
                                    }
                                    
                                    $labelInfo .= '<span>'.$tempLabel.$value.'</span>';
                                    
                                    if($label['lineBreak'])
                                        $labelInfo .= '<br />';
                                     else
                                        $labelInfo .= ' <strong>|</strong> ';   
                                }
                            }
                            
                            if($data['additional_notes'])
                                $labelInfo .= '<div><strong>Note : '.$data['additional_notes'].'</strong></div>';

                            $infoBox = "<div style='margin-left: 10px;".(($labelW - $data['logo_width'])>80?"float:left;min-width: 80mm":"width: 80mm;")."font-size: ".$data['font_size']."px;'>".$labelInfo."</div>";
                            
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
                            
                            $content .= "<div style='margin-left: ".$marginLeft."mm;padding: 5px;border: 1px solid #ccc;float:left;width: ".($labelW-3)."mm;background: url(images/labelLogo.png) no-repeat right bottom;padding: 5px;'>".$labelHtml."</div>";
                            
                            $index++;
                            if ($index > count($labels) - 1) {
                                break;
                            }
                            
                        }
                        $content .= "</div>";
                        
                        $index++;
                        if ($index > count($labels))
                            break;
                        else {
                            $index--;
                        }
                    }
                    $content .= "<div>";
                    
                    if ($index > count($labels))
                        break;
                    //else
                      //  $index--;
                }
            }
            
            $pdf->WriteHTML($content);
            
            $file_download = "temp/preview_template_".date("Ymd_His").".pdf";
            $pdf->Output($file_download, 'f');
            return $file_download;

        }
        else {
            throw new \yii\web\HttpException(404, 'We have not found your request.');
        }
    }
    
    public function actionPrinttimeattendancelabel()
        {
        $uids = \Yii::$app->request->post("uid");
        $label_tempalte = \Yii::$app->request->post("label_template");
        
        $labelTemplate = \backend\models\LabelTemplates::findOne(['id' => $label_tempalte]);
        
        if(!isset($_GET['expand']))
            $_GET['expand'] = "project_level,project";
        else
            $_GET['expand'] = "project_level,project,".$_GET['expand'];
        
        if(count($uids) && $labelTemplate) {
            
            $template_param = \yii\helpers\ArrayHelper::toArray($labelTemplate);
            
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
            
            $fileManager = new \backend\models\FileManager();
            
            $nfcLogo = 'images/nfc.png';

            $imageUrl = "images/logo.png";

            $labels = array_values($uids);

            if (isset($labels)) {
                
                $index = 0;
                $content = "";
                $imageUrl = "images/logo.png";
                $pdf = new mPDF('', array($data['pageWidth'], $data['pageHeight']));
        
                $pdf->SetAutoPageBreak(true);
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
                
                while (1) {
                    //-----------------------------Print Label-----------------------------------------------------------------
                    //$pdf->AddPage();
                    
                    $content .= "<div style='margin-left: 0;width: 100%;'>";

                    for ($vindex = 0; $vindex < $data['numberVertical']; $vindex++) {
                        if($vindex)
                            $marginTop = $data['vSpace'] / 2;
                        $content .= "<div class='testClass' style='margin-top: ".$marginTop."mm;clear:both;'>";
            
                        for ($hindex = 0; $hindex < $data['numberHorizontal']; $hindex++) {
                            
                            $marginLeft = 0;
                            if($hindex)
                                $marginLeft = $data['hSpace'] / 2;
                            
                            $tagsQuery = \backend\models\Tags::find()->andWhere(['uid' => $labels[$index]])->one();
                            
                            $serializer = new \backend\models\CustomSerializer();

                            $tagDetails = $serializer->serialize($tagsQuery);
                            
                            if(!count($tagDetails)) {
                                $index++;
                                continue;
                            }
                            
                            $codeImage = "";
                            
                            if($data['type']=='qr' || $data['type']=='bar') {
                                $codeImage = $fileManager->getPath($data['type']."code")."/".$tagDetails['uid'].".png";
                                
                                if(!file_exists($codeImage))
                                    $codeImage = "images/noimage.png";
                            }
                            else if($data['type']=='nfc' && isset($data['logo']) && file_exists($data['logo']))
                                $codeImage = $data['logo'];
                            else if($data['type']=='nfc' && isset($data['logo'])) {
                                $logoFilename = array_pop(preg_split("/((=)|(\/))/", $data['logo']));
                                $fileManager = new \backend\models\FileManager();
                                $codeImage = $fileManager->getPath("")."/".$logoFilename;
                            }
                            else
                                $codeImage = 'images/nfc.png';
                            
                            $labelInfo = "";
                            
                            foreach($template_param['checked_labels'] as $label) {
                                if($label['isChecked']) {
                                    $tempLabel = "";
                                    if($label['showLabel'])
                                        $tempLabel = '<strong>'.$label['label'].'</strong> : ';
                                    
                                    $value = "";
                                    switch ($label['name']) {
                                        case 'company_name': 
                                            $value = $tagDetails['project']['company_name'];
                                            break;
                                        case 'project_name': 
                                            $value = $tagDetails['project']['project_name'];
                                            break;                                        
                                        case 'client_name': 
                                            $value = $tagDetails['project']['client_name'];
                                            break;
                                        case 'project_address': 
                                            $value = $tagDetails['project']['project_address']." ".$tagDetails['project']['project_city']." ".$tagDetails['project']['project_country'];
                                            break;
                                        case 'client_location': 
                                            $value = $tagDetails['project']['client_location'];
                                            break;
                                        case 'project_location': 
                                            $value = $tagDetails['project']['project_location'];
                                            break;
                                        case 'main_contractor': 
                                            $value = $tagDetails['project']['main_contractor'];
                                            break;
                                        case 'project_level': 
                                            $value = implode(" > ", $tagDetails['project_level']);
                                            break;
                                        default: 
                                            $value .= $tagDetails[$label['name']];
                                    }
                                    
                                    $labelInfo .= '<span>'.$tempLabel.$value.'</span>';
                                    
                                    if($label['lineBreak'])
                                        $labelInfo .= '<br />';
                                     else
                                        $labelInfo .= ' <strong>|</strong> ';   
                                }
                            }
                            
                            if($data['additional_notes'])
                                $labelInfo .= '<div><strong>Note : '.$data['additional_notes'].'</strong></div>';

                            $infoBox = "<div style='margin-left: 10px;".(($labelW - $data['logo_width'])>80?"float:left;min-width: 80mm":"width: 80mm;")."font-size: ".$data['font_size']."px;'>".$labelInfo."</div>";

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

                            $logoBox = "<div id='logoContainer' style='".$logoStyle."text-align:center;'>UID: 4SOMQ95506<br /><img src='".$codeImage."' style='width: ".$data['logo_width']."mm;' /><br />http://sitetrack-nfc.com</div>";

                            $labelHtml = "";

                            if($data['logo_position']=='bottomLeft' || $data['logo_position']=='bottomRight' || $data['logo_position']=='bottomMiddle') {
                                $labelHtml = $infoBox.$logoBox;
                            }
                            else {
                                $labelHtml = $logoBox.$infoBox;
                            }
                            
                            $content .= "<div style='margin-left: ".$marginLeft."mm;padding: 5px;border: 1px solid #ccc;float:left;width: ".($labelW-3)."mm;background: url(images/labelLogo.png) no-repeat right bottom;padding: 5px;'>".$labelHtml."</div>";
                            
                            $index++;
                            if ($index > count($labels) - 1) {
                                break;
                            }
                            
                        }
                        $content .= "</div>";
                        
                        $index++;
                        if ($index > count($labels))
                            break;
                        else {
                            $index--;
                        }
                    }
                    $content .= "<div>";
                    
                    if ($index > count($labels))
                        break;
                    //else
                      //  $index--;
                }
            }
            
            $pdf->WriteHTML($content);
            
            $file_download = "temp/preview_template_".date("Ymd_His").".pdf";
            $pdf->Output($file_download, 'f');
            return $file_download;

        }
        else {
            throw new \yii\web\HttpException(404, 'We have not found your request.');
        }
    }
    
    public function actionPrintTagReport($tagId, $reportTemplate)
    {
        
        $tagQueryObj = \backend\models\Tags::find()->andWhere(['id' => $tagId]);
        
        $_GET['expand'] = "project_level,itemObj,processObj,userGroup,company,project";
        
        $templateObj = \backend\models\ReportTemplates::findOne($reportTemplate);
        
        $serializer = new \backend\models\CustomSerializer();
            
        $tagArray = $serializer->serialize($tagQueryObj->one());
        
        $template_param = $serializer->serialize($templateObj);
        
        //-----------------------------Set hardcoded values if not set---------------------------------------------------------------
        $data['leftMargin'] = isset($template_param['left_margin']) ? (int) $template_param['left_margin'] : 5;
        $data['topMargin'] = isset($template_param['top_margin']) ? (int) $template_param['top_margin'] : 5;
        $data['rightMargin'] = isset($template_param['right_margin']) ? (int) $template_param['right_margin'] : 5;
        $data['bottomMargin'] = isset($template_param['bottom_margin']) ? (int) $template_param['bottom_margin'] : 5;

        $data['pageWidth'] = isset($template_param['page_width']) ? (int) $template_param['page_width'] : 216;
        $data['pageHeight'] = isset($template_param['page_height']) ? (int) $template_param['page_height'] : 279;
        
        $data['qrCodeWidth'] = isset($template_param['qr_code_width']) ? (int) $template_param['qr_code_width'] : 100;
        $data['barCodeWidth'] = isset($template_param['bar_code_width']) ? (int) $template_param['bar_code_width'] : 150;
        $data['projectlogoWidth'] = isset($template_param['project_logo_width']) ? (int) $template_param['project_logo_width'] : 100;
        $data['projectImageWidth'] = isset($template_param['project_image_width']) ? (int) $template_param['project_image_width'] : 100;
        
        $fileManager = new \backend\models\FileManager();
        
        $projectLevel = "";
        foreach($tagArray['project_level'] as $key => $val) {
            if($key)
                $projectLevel .= " > ";
            $projectLevel .= $val;
        }
        
        $items = "";
        foreach($tagArray['itemObj'] as $key => $val) {
            if($key)
                $items .= " > ";
            $items .= $val['item_name'];
        }
        
        $process = "";
        foreach($tagArray['processObj'] as $key => $val) {
            if($key)
                $process .= " > ";
            $process .= $val['process_name'];
        }
        
        $noImageUrl = "images/noimage.png";
        
        $projectLogoUrl = $fileManager->getPath("project_image")."/".$tagArray['project']['project_logo'];
        if(file_exists($projectLogoUrl))
            $projectLogoUrl = $noImageUrl;
        
        $projectImageUrl = $fileManager->getPath("project_image")."/".$tagArray['project']['project_image'];
        if(file_exists($projectImageUrl))
            $projectImageUrl = $noImageUrl;
        
        $sampleLabels = [
            '[[tag_name]]' => $tagArray['tag_name'],
            
            '[[project_logo]]' => "<img src='".$projectLogoUrl."' width='".$template_param['project_logo_width']."' />",
            '[[project_image]]' => "<img src='".$projectImageUrl."' width='".$template_param['project_image_width']."' />",
            
            '[[image_qr_code]]' => "<img src='".$fileManager->getPath("qrcode")."/".$tagArray['uid'].".png' alt='QR Code' border='1' width='".$template_param['qr_code_width']."' />",
            '[[image_bar_code]]' => "<img src='".$fileManager->getPath("barcode")."/".$tagArray['uid'].".png' alt='QR Code' border='1' width='".$template_param['bar_code_width']."' />",
            
            '[[company_name]]' => $tagArray['company']['company_name'],
            '[[client_name]]' => $tagArray['project']['client_name'],
            '[[client_location]]' => $tagArray['project']['client_address'].", ".$tagArray['project']['client_city'],
            '[[main_contractor]]' => $tagArray['project']['main_contractor'],
            '[[project_name]]' => $tagArray['project']['project_name'],
            '[[project_address]]' => $tagArray['project']['project_address'].", ".$tagArray['project']['project_city'],
            '[[area]]' => $tagArray['project']['project_location'],
            
            '[[client_project_manager]]' => $tagArray['project']['client_project_manager'],
            '[[project_manager]]' => $tagArray['project']['project_manager'],
            '[[consultant]]' => $tagArray['project']['consultant'],
            '[[project_director]]' => $tagArray['project']['project_director'],
            '[[consultant_project_manager]]' => $tagArray['project']['consultant_project_manager'],
            '[[contractor_project_manager]]' => $tagArray['project']['contractor_project_manager'],
            
            '[[page_number]]' => $template_param['page_number_prefix']."1".$template_param['page_number_suffix'],
            
            '[[project_level]]' => $projectLevel,
            '[[items]]' => $items,
            '[[process]]' => $process,
            '[[unique_code]]' => $tagArray['uid'],
            '&nbsp;' => " ",
            '/filemanager/getimage?type=YnJvd3Nl&amp;file=' => $fileManager->getPath("browse")."/",
            '[[tag_description]]' => $tagArray['tag_description']
        ];
        
        $content = $template_param['content'];
        
        $content = str_replace(array_keys($sampleLabels), $sampleLabels, $content);
                
        $pdf = new mPDF('', array($data['pageWidth'], $data['pageHeight']));
        
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(false);
        $pdf->HREF = '';
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetLeftMargin($data['leftMargin']);
        $pdf->SetRightMargin($data['rightMargin']);
        $pdf->SetTopMargin($data['topMargin']);
        
        $pdf->WriteHTML($content);
        
        $pdf->Output();
        exit;
    }
    
    public function actionPrintTagReports()
    {
        
        $tagIds = \Yii::$app->request->post("tags");
        if(!$tagIds) {
            \yii::$app->getResponse()->setStatusCode(500);
            return "Please select a tag to generate report.";
        }
            
        $tagQueryObj = \backend\models\Tags::find()->andWhere(['id' => $tagIds]);
        $_GET['expand'] = "project_level,itemObj,processObj,userGroup,company,project";
        $dataProvider = new ActiveDataProvider([
            'query' => $tagQueryObj,
            'pagination' => false
        ]);
        
        $serializer = new \backend\models\CustomSerializer();    
        $tagsObj = $serializer->serialize($dataProvider);
        
        $reportTemplate = \Yii::$app->request->post("reportTemplate");
        $templateObj = \backend\models\ReportTemplates::findOne($reportTemplate);
        $template_param = $serializer->serialize($templateObj);
        
        //-----------------------------Set hardcoded values if not set---------------------------------------------------------------
        $data['leftMargin'] = isset($template_param['left_margin']) ? (int) $template_param['left_margin'] : 5;
        $data['topMargin'] = isset($template_param['top_margin']) ? (int) $template_param['top_margin'] : 5;
        $data['rightMargin'] = isset($template_param['right_margin']) ? (int) $template_param['right_margin'] : 5;
        $data['bottomMargin'] = isset($template_param['bottom_margin']) ? (int) $template_param['bottom_margin'] : 5;

        $data['pageWidth'] = isset($template_param['page_width']) ? (int) $template_param['page_width'] : 216;
        $data['pageHeight'] = isset($template_param['page_height']) ? (int) $template_param['page_height'] : 279;
        
        $data['qrCodeWidth'] = isset($template_param['qr_code_width']) ? (int) $template_param['qr_code_width'] : 100;
        $data['barCodeWidth'] = isset($template_param['bar_code_width']) ? (int) $template_param['bar_code_width'] : 150;
        $data['projectlogoWidth'] = isset($template_param['project_logo_width']) ? (int) $template_param['project_logo_width'] : 100;
        $data['projectImageWidth'] = isset($template_param['project_image_width']) ? (int) $template_param['project_image_width'] : 100;
        
        $pdf = new mPDF('', array($data['pageWidth'], $data['pageHeight']));
        
        $pdf->SetAutoPageBreak(false);
        $pdf->HREF = '';
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetLeftMargin($data['leftMargin']);
        $pdf->SetRightMargin($data['rightMargin']);
        $pdf->SetTopMargin($data['topMargin']);
        
        $fileManager = new \backend\models\FileManager();
        
        foreach($tagsObj as $tagKey => $tagArray) {
            
            $pdf->AddPage();
            
            $projectLevel = "";
            foreach($tagArray['project_level'] as $key => $val) {
                if($key)
                    $projectLevel .= " > ";
                $projectLevel .= $val;
            }

            $items = "";
            foreach($tagArray['itemObj'] as $key => $val) {
                if($key)
                    $items .= " > ";
                $items .= $val['item_name'];
            }

            $process = "";
            foreach($tagArray['processObj'] as $key => $val) {
                if($key)
                    $process .= " > ";
                $process .= $val['process_name'];
            }

            $noImageUrl = "images/noimage.png";

            $projectLogoUrl = $fileManager->getPath("project_image")."/".$tagArray['project']['project_logo'];
            if(file_exists($projectLogoUrl))
                $projectLogoUrl = $noImageUrl;

            $projectImageUrl = $fileManager->getPath("project_image")."/".$tagArray['project']['project_image'];
            if(file_exists($projectImageUrl))
                $projectImageUrl = $noImageUrl;

            $sampleLabels = [
                '[[tag_name]]' => $tagArray['tag_name'],

                '[[project_logo]]' => "<img src='".$projectLogoUrl."' width='".$template_param['project_logo_width']."' />",
                '[[project_image]]' => "<img src='".$projectImageUrl."' width='".$template_param['project_image_width']."' />",

                '[[image_qr_code]]' => "<img src='".$fileManager->getPath("qrcode")."/".$tagArray['uid'].".png' alt='QR Code' border='1' width='".$template_param['qr_code_width']."' />",
                '[[image_bar_code]]' => "<img src='".$fileManager->getPath("barcode")."/".$tagArray['uid'].".png' alt='QR Code' border='1' width='".$template_param['bar_code_width']."' />",

                '[[company_name]]' => $tagArray['company']['company_name'],
                '[[client_name]]' => $tagArray['project']['client_name'],
                '[[client_location]]' => $tagArray['project']['client_address'].", ".$tagArray['project']['client_city'],
                '[[main_contractor]]' => $tagArray['project']['main_contractor'],
                '[[project_name]]' => $tagArray['project']['project_name'],
                '[[project_address]]' => $tagArray['project']['project_address'].", ".$tagArray['project']['project_city'],
                '[[area]]' => $tagArray['project']['project_location'],

                '[[client_project_manager]]' => $tagArray['project']['client_project_manager'],
                '[[project_manager]]' => $tagArray['project']['project_manager'],
                '[[consultant]]' => $tagArray['project']['consultant'],
                '[[project_director]]' => $tagArray['project']['project_director'],
                '[[consultant_project_manager]]' => $tagArray['project']['consultant_project_manager'],
                '[[contractor_project_manager]]' => $tagArray['project']['contractor_project_manager'],

                '[[page_number]]' => $template_param['page_number_prefix'].($tagKey+1).$template_param['page_number_suffix'],

                '[[project_level]]' => $projectLevel,
                '[[items]]' => $items,
                '[[process]]' => $process,
                '[[unique_code]]' => $tagArray['uid'],
                '&nbsp;' => " ",
                '/filemanager/getimage?type=YnJvd3Nl&amp;file=' => $fileManager->getPath("browse")."/",
                '[[tag_description]]' => $tagArray['tag_description']
            ];

            $content = $template_param['content'];

            $content = str_replace(array_keys($sampleLabels), $sampleLabels, $content);

            $pdf->WriteHTML($content);
        }
        
        $file_download = "temp/report_tags_". date("d-M-Y-H-i-s"). uniqid().".pdf";
        $pdf->Output($file_download, "f");
        return $file_download;
    }
    
    function calculatedLabelW($data) {

        $w = 0;
        if ($data['numberHorizontal'] != 0 && $data['numberVertical'] != 0)
            $w = ($data['pageWidth'] - $data['leftMargin'] - $data['rightMargin'] - ($data['numberHorizontal'] - 1) * $data['hSpace']) / $data['numberHorizontal'];

        return $w;
    }

    function calculatedLabelH($data) {
        $h = 0;
        if ($data['numberHorizontal'] != 0 && $data['numberVertical'] != 0)
            $h = ($data['pageHeight'] - $data['topMargin'] - $data['bottomMargin'] - ($data['numberVertical'] - 1) * $data['vSpace']) / $data['numberVertical'];
        
        return $h;
    }
    
}