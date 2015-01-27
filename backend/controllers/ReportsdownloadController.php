<?php

namespace backend\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use backend\models\customPDF\printLabel;

class ReportsdownloadController extends Controller
{
    
    public $enableCsrfValidation = false;
        
    public function actionPreviewtemplate()
    {
        $template_param = \Yii::$app->request->post();
     
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
        $data['qr_height'] = isset($template_param['logo_width']) ? (int) $template_param['logo_width'] : 15;
        $data['type'] = isset($template_param['print_type']) ? $template_param['print_type'] : 'qr';
        $data['font_size'] = isset($template_param['font_size']) ? (int) $template_param['font_size'] : 5;
        $data['logo_height'] = isset($template_param['logo_height']) ? (int) $template_param['logo_height'] : 5;
        
        if (!empty($template_param['logo']))
            $data['logo'] = isset($template_param['logo']) ? $template_param['logo'] : '';

        elseif (!empty($template_param['old_logo_file']))
            $data['logo'] = isset($template_param['old_logo_file']) ? $template_param['old_logo_file'] : '';

        //-----------------------------end--------------------------------------------------------------------------
        //
        //if (isset($labels[0]['logo']) && file_exists(UPLOAD_DOC_PATH . '/' . $labels[0]['logo'])) {
        //    $imageUrl = 'http://' . $_SERVER['HTTP_HOST'] . $this->Html->assetUrl('app/webroot/img') . '/logo.png';
        //} else {
        //    $imageUrl = 'http://' . $_SERVER['HTTP_HOST'] . $this->Html->assetUrl('app/webroot/img') . '/logo.png';
        //}
        //
        //if ($data['type'] == 'NFC' && isset($data['logo']) && $data['logo'] != "") {
        //    $nfcLogo = 'http://' . $_SERVER['HTTP_HOST'] . $this->Html->assetUrl('app/webroot/img') . '/user_upload/' . $data['logo'];
        //} else {
        //    $nfcLogo = 'http://' . $_SERVER['HTTP_HOST'] . $this->Html->assetUrl('app/webroot/img') . '/nfc.png';
        //}
        
        //if($data['type']=='bar')
            $nfcLogo = 'images/bar.png';
//        else if($data['type']=='nfc')
//            $nfcLogo = 'images/' . $data['logo'];

        $imageUrl = "images/logo.png";
        if (isset($labels)) {
            $index = 0;
            if ($data['pageWidth'] <= $data['pageHeight'])
                $pdf = new printLabel('p', 'mm', array($data['pageWidth'], $data['pageHeight']), $data);
            else
                $pdf = new printLabel('l', 'mm', array($data['pageWidth'], $data['pageHeight']), $data);
            while (1) {
        //-----------------------------Print Label-----------------------------------------------------------------
                $pdf->AddPage();
                $pdf->SetAutoPageBreak(false);
                $pdf->SetFont('Arial', 'B', $pdf->font_size);
                $pdf->SetDrawColor(34, 53, 519);
                $pdf->SetLeftMargin($pdf->l_margin);
                $pdf->SetRightMargin($pdf->r_margin);
                $pdf->SetTopMargin($pdf->t_margin);
                $labelW = $pdf->calculatedLabelW();
                $labelH = $pdf->calculatedLabelH();
                $textLabels = $labels;
                $labelW = $pdf->calculatedLabelW();
                $labelH = $pdf->calculatedLabelH();
                $x = $pdf->l_margin;
                $y = $pdf->t_margin;

                for ($vindex = 0; $vindex < $data['numberVertical']; $vindex++) {
                    $x = $pdf->l_margin;
                    for ($hindex = 0; $hindex < $data['numberHorizontal']; $hindex++) {
                        $pdf->SetFont('Arial', 'B', $pdf->font_size);
                        $pdf->SetXY($x, $y);
                        $pdf->Cell($labelW, $labelH, ' ', 0);
                        $pdf->SetXY($x + $pdf->getProportionalX(6), $y + $pdf->getProportionalY(8));
                        $len_x = $pdf->get_remain_x($x, $pdf->GetX(), $data['qr_height']);
                        $z = $pdf->setapprFontSize($len_x, 'UID: ' . $labels[$index]['unique code:'], $pdf->font_size);
                        $len_y = $pdf->get_remain_y($y, $pdf->GetY(), $z);
                        if ($this->request->data['Task']['check_unique_code'] == 1)
                            $pdf->Cell($len_x, $len_y - (($len_y * 50) / 100), 'UID: ' . $labels[$index]['unique code:'], 0, 1, 'C');
        //-----------------------------QR-code and NFC AREA---------------------------------------------------------------     
                        $inc = (($pdf->font_size * 50) / 100) >= 9 ? 9 : ($pdf->font_size * 50) / 100;

                        $pdf->SetXY($x + $pdf->getProportionalX(7), $pdf->GetY() + 0.5);

                        if ($this->request->data['Task']['print_type'] == "QR") {

                            $len_x = $pdf->get_remain_x($x, $pdf->GetX(), $data['qr_height']);
                            $len_y = $pdf->get_remain_y($y, $pdf->GetY(), $data['qr_height']);
                            $QR_hw = ($len_x < $len_y) ? $len_x : $len_y;
                            $pdf->Image("images/bar-code.jpg", $pdf->GetX(), $pdf->GetY(), $QR_hw, $QR_hw, 'PNG');
                            $pdf->SetXY($x + $pdf->getProportionalX(6), $pdf->GetY() + $QR_hw + 0.5);
                            $pdf->SetTextColor(111, 106, 106);
                            $pdf->SetFont('Arial', '', $pdf->font_size);

                            $len_x = $pdf->get_remain_x($x, $pdf->GetX(), $QR_hw);
                            $z = $pdf->setapprFontSize((($len_x * 80) / 100), 'QR Code', $pdf->font_size);
                            $len_y = $pdf->get_remain_y($y, $pdf->GetY(), $z);

                            $pdf->Cell($len_x, $len_y - (($len_y * 50) / 100), 'QR Code', 0, 0, 'C');
                            $pdf->SetFont('Arial', 'B', $pdf->font_size);
                        } else {
                            $len_x = $pdf->get_remain_x($x, $pdf->GetX(), $data['qr_height']);
                            $len_y = $pdf->get_remain_y($y, $pdf->GetY(), $data['qr_height']);

                            $NFC_hw = ($len_x < $len_y) ? $len_x : $len_y;
                            $pdf->Image($nfcLogo, $pdf->GetX(), $pdf->GetY(), $NFC_hw, $NFC_hw);

                            $pdf->SetXY($x + $pdf->getProportionalX(6), $pdf->GetY() + $NFC_hw + 0.5);
                            $pdf->SetTextColor(111, 106, 106);
                            $pdf->SetFont('Arial', '', $pdf->font_size);

                            $len_x = $pdf->get_remain_x($x, $pdf->GetX(), $NFC_hw);
                            $z = $pdf->setapprFontSize((($len_x * 50) / 100), 'Tap to Read NFC Tag', $pdf->font_size);
                            $len_y = $pdf->get_remain_y($y, $pdf->GetY(), $z);
                            $pdf->Cell($len_x, $len_y - (($len_y * 50) / 100), 'Tap to Read NFC Tag', 0, 1, 'C');
                            $pdf->SetFont('Arial', 'B', $pdf->font_size);
                        }
                        $pdf->SetTextColor(0, 0, 0);
        ////-----------------------------LOGO---------------------------------------------------------------     

                        $pdf->SetXY($x + $pdf->getProportionalX(6), $pdf->GetY() + $pdf->getProportionalY(7 + $inc + 2));
                        $len_x = $pdf->get_remain_x($x, $pdf->GetX(), $data['qr_height']);

                        // $len_y = $pdf->get_remain_y($y, $pdf->GetY(), ($len_x * 4) / 15);
                        $len_y = $pdf->get_remain_y($y, $pdf->GetY(), ((52 / 222) * $len_x));
                        $pdf->Image($imageUrl, $pdf->GetX(), $pdf->GetY(), $len_x, $len_y);
        //-----------------------------Link---------------------------------------------------------------     
                        $pdf->SetXY($x + $pdf->getProportionalX(6), $pdf->GetY() + $len_y + 1);
                        $len_x = $pdf->get_remain_x($x, $pdf->GetX(), $data['qr_height']);
                        $z = $pdf->setapprFontSize($len_x, 'www.sitetrack-nfc.com', $pdf->font_size);
                        $len_y = $pdf->get_remain_y($y, $pdf->GetY(), $z);
                        $pdf->Cell($len_x, $len_y - (($len_y * 50) / 100), 'www.sitetrack-nfc.com', 0, 0, 'C');

        //-------------------------Text Printing-----------------------------------------------------------
                        $pdf->SetFont('Arial', 'B', $pdf->font_size);
                        $k = $pdf->getProportionalY(6);
                        $left_space = $data['qr_height'];
                        $label_width = $pdf->GetStringWidth("Location:  ");
                        foreach ($labels[$index] as $key => $value) {
                            if ($key != "logo" && $key != "unique code:") {
                                $pdf->SetXY($x + $left_space + $pdf->getProportionalX(15), $y + $k);
                                $pdf->SetFont('Arial', 'B', $pdf->font_size);
                                $len_x = $pdf->get_remain_x($x, $pdf->GetX(), $label_width);
                                $len_y = $pdf->get_remain_y($y, $pdf->GetY(), $pdf->font_size);

                                if ($key == 'Note:') {
                                    //FIRE RESISTANCE RATED FIRESTOP
                                      $pdf->SetTextColor(255, 0, 0);
                                    $note_data = explode('<br/>', $value);
                                    $pdf->Cell($len_x, $len_y, $pdf->getPrintableStr((string) "FIRE RESI", $len_x, $len_y), 0);
        //                            for ($i = 0; $i <= 3; $i++) {
                                    $label_text_width = $pdf->GetStringWidth("STANCE FIRESTOP   ");
                                    $len_x = $pdf->get_remain_x($x, $pdf->GetX(), $label_text_width);
                                    $len_y = $pdf->get_remain_y($y, $pdf->GetY(), $pdf->font_size);


                                    $pdf->SetFont('Arial', 'B', $pdf->font_size);

                                    if ($len_y == $pdf->font_size)
                                        $pdf->Cell($len_x, $len_y, $pdf->getPrintableStr((string) trim("STANCE FIRESTOP"), $len_x, $len_y), 0);
                                    $k = $k + $inc;
                                    $pdf->SetXY($x + $left_space + $pdf->getProportionalX(15) + $label_width, $y + $k);

        //                            }
                                } else {

                                    $pdf->SetFont('Arial', 'B', $pdf->font_size);
                                    $pdf->SetTextColor(0, 0, 0);

                                    if ($len_y == $pdf->font_size)
                                        $pdf->Cell($len_x, $len_y, $pdf->getPrintableStr((string) $key, $len_x, $len_y), 0);
                                    else
                                        $len_y = 0;
                                    $pdf->SetTextColor(111, 106, 106);
                                    $pdf->SetFont('Arial', '', $pdf->font_size);

                                    if ($len_y != 0)
                                        $pdf->SetXY($x + $left_space + $pdf->getProportionalX(15) + $label_width, $y + $k);
                                    $len_x = $pdf->get_remain_x($x, $pdf->GetX(), $pdf->getProportionalX(35));
                                    $len_y = $pdf->get_remain_y($y, $pdf->GetY(), $pdf->font_size);
                                    if ($len_y == $pdf->font_size)
                                        $pdf->Cell($len_x, $len_y, $pdf->getPrintableStr((string) $value, $len_x, $len_y), 0);
                                    $pdf->SetTextColor(0, 0, 0);
                                    $k = $k + $inc;
                                }
                            }
                        }
                        $x+=$labelW + $pdf->hr_space;
                        $index++;
                        if ($index > count($labels) - 1) {
                            break;
                        }
                    }
                    $y+= $labelH + $pdf->vr_space;

                    $index++;
                    if ($index > count($labels))
                        break;
                    else {
                        $index--;
                    }
                }

                if ($index > count($labels))
                    break;
                //else
                  //  $index--;
            }
        }
        else {
            $sampleLabels = array('Company:' => '------25 characters------',
                'Project:' => 'Red Avenue Building 2ndf',
                'Owner:' => 'Al Hatimi Trading FZEXXX',
                'Address:' => '203,Red Avenue BuildingX',
                'Tel:' => '00-9714-2955681/2955682',
                'Site:' => 'Red Avenue Building 2ndf',
                'Area:' => 'First floor,Electrical Rm',
                'Location:' => 'Above False Ceiling ACXX',
                'Sub-Loc:' => 'Inside AC duct low levelX',
                'Summary:' => 'First Fix of AC duct chk'
            );

            $textFields = array('', 'Al Hatimi', 'Al Hatimi', 'Sharjaha', '9854578285828',
                'Abu Dhabi', 'Abu Dhabi', 'Abu Dhabi', 'False Celling', 'False Celling', 'Text-11:', 'Text-12:');

            if ($data['pageWidth'] <= $data['pageHeight'])
                $pdf = new printLabel('p', 'mm', array($data['pageWidth'], $data['pageHeight']), $data);
            else
                $pdf = new printLabel('l', 'mm', array($data['pageWidth'], $data['pageHeight']), $data);

            $pdf->AddPage();
            $pdf->SetAutoPageBreak(false);
            $pdf->SetFont('Arial', 'B', $pdf->font_size);
            $pdf->SetDrawColor(34, 53, 519);
            $pdf->SetLeftMargin($pdf->l_margin);
            $pdf->SetRightMargin($pdf->r_margin);
            $pdf->SetTopMargin($pdf->t_margin);
            $labelW = $pdf->calculatedLabelW();
            $labelH = $pdf->calculatedLabelH();
            $labelW = $pdf->calculatedLabelW();
            $labelH = $pdf->calculatedLabelH();
            $x = $pdf->l_margin;
            $y = $pdf->t_margin;

            for ($vindex = 0; $vindex < $data['numberVertical']; $vindex++) {
                $x = $pdf->l_margin;
                for ($hindex = 0; $hindex < $data['numberHorizontal']; $hindex++) {
                    $pdf->SetFont('Arial', 'B', $pdf->font_size);
                    $pdf->SetXY($x, $y);
                    $pdf->Cell($labelW, $labelH, '', 1);
                    $pdf->SetXY($x + $pdf->getProportionalX(6), $y + $pdf->getProportionalY(8));
                    $len_x = $pdf->get_remain_x($x, $pdf->GetX(), $data['qr_height']);

                    $z = $pdf->setapprFontSize($len_x, 'UID: 290D930028', $pdf->font_size);
                    $len_y = $pdf->get_remain_y($y, $pdf->GetY(), $z);
                    $pdf->Cell($len_x, $len_y - (($len_y * 50) / 100), 'UID: 290D930028', 0, 1, 'C');
                    $pdf->SetFont('Arial', '', $pdf->font_size);
        //-----------------------------QR-code and NFC AREA---------------------------------------------------------------     
                    $pdf->SetXY($x + $pdf->getProportionalX(7), $pdf->GetY() + 0.5);
                    $inc = (($pdf->font_size * 50) / 100) >= 8 ? 8 : ($pdf->font_size * 50) / 100;
                    if ($data['type'] == "qr") {

                        $len_x = $pdf->get_remain_x($x, $pdf->GetX(), $data['qr_height']);
                        $len_y = $pdf->get_remain_y($y, $pdf->GetY(), $data['qr_height']);

                        $QR_hw = ($len_x < $len_y) ? $len_x : $len_y;
                        $pdf->Image("images/bar-code.jpg" ,$pdf->GetX(), $pdf->GetY(), $QR_hw, $QR_hw, 'jpg');
                        $pdf->SetXY($x + $pdf->getProportionalX(6), $pdf->GetY() + $QR_hw + 0.5);
                        $pdf->SetTextColor(111, 106, 106);
                        $pdf->SetFont('Arial', '', $pdf->font_size);
                        $len_x = $pdf->get_remain_x($x, $pdf->GetX(), $QR_hw);
                        $z = $pdf->setapprFontSize((($len_x * 80) / 100), 'QR Code', $pdf->font_size);
                        $len_y = $pdf->get_remain_y($y, $pdf->GetY(), $z);
                        $pdf->Cell($len_x, $len_y - (($len_y * 50) / 100), 'QR Code', 0, 0, 'C');
                        $pdf->SetFont('Arial', 'B', $pdf->font_size);
                    } else {

                        //list($nfc_logo_width, $nfc_logo_height) = getimagesize($filename);

                        $len_x = $pdf->get_remain_x($x, $pdf->GetX(), $data['qr_height']);
                        $len_y = $pdf->get_remain_y($y, $pdf->GetY(), $data['qr_height']);

                        $NFC_hw = ($len_x < $len_y) ? $len_x : $len_y;
                        $pdf->Image($nfcLogo, $pdf->GetX(), $pdf->GetY(), $NFC_hw, $NFC_hw);

                        $pdf->SetXY($x + $pdf->getProportionalX(6), $pdf->GetY() + $NFC_hw + 0.5);
                        $pdf->SetTextColor(111, 106, 106);
                        $pdf->SetFont('Arial', '', $pdf->font_size);

                        $len_x = $pdf->get_remain_x($x, $pdf->GetX(), $NFC_hw);
                        $z = $pdf->setapprFontSize((($len_x * 50) / 100), 'Tap to Read NFC Tag', $pdf->font_size);
                        $len_y = $pdf->get_remain_y($y, $pdf->GetY(), $z);
                        $pdf->Cell($len_x, $len_y - (($len_y * 50) / 100), 'Tap to Read NFC Tag', 0, 0, 'C');
                        $pdf->SetFont('Arial', 'B', $pdf->font_size);
                    }
                    $pdf->SetTextColor(0, 0, 0);
        ////-----------------------------LOGO---------------------------------------------------------------     
                    $pdf->SetXY($x + $pdf->getProportionalX(6), $pdf->GetY() + $pdf->getProportionalY(7 + $inc + 2));
                    $len_x = $pdf->get_remain_x($x, $pdf->GetX(), $data['qr_height']);

                    //$len_y = $pdf->get_remain_y($y, $pdf->GetY(), ($len_x * 4) / 15);
                    $len_y = $pdf->get_remain_y($y, $pdf->GetY(), ((52 / 222) * $len_x));
                    $pdf->Image($imageUrl, $pdf->GetX(), $pdf->GetY(), $len_x, $len_y);
        //-----------------------------Link---------------------------------------------------------------     

                    $pdf->SetXY($x + $pdf->getProportionalX(6), $pdf->GetY() + $len_y + 1);
                    $len_x = $pdf->get_remain_x($x, $pdf->GetX(), $data['qr_height']);
                    $z = $pdf->setapprFontSize($len_x, 'www.sitetrack-nfc.com', $pdf->font_size);
                    $len_y = $pdf->get_remain_y($y, $pdf->GetY(), $z);
                    $pdf->Cell($len_x, $len_y - (($len_y * 50) / 100), 'www.sitetrack-nfc.com', 0, 0, 'C');

        //-------------------------Text Printing-----------------------------------------------------------
                    $pdf->SetFont('Arial', 'B', $pdf->font_size);
                    $k = $pdf->getProportionalY(7);
                    $left_space = $data['qr_height'];
                    $label_width = $pdf->GetStringWidth("Location:  ");
                    foreach ($sampleLabels as $key => $value) {
                        if ($key != "logo" && $key != "unique code:") {
                            $pdf->SetXY($x + $left_space + $pdf->getProportionalX(15), $y + $k);
                            $pdf->SetFont('Arial', 'B', $pdf->font_size);

                            $len_x = $pdf->get_remain_x($x, $pdf->GetX(), $label_width);
                            $len_y = $pdf->get_remain_y($y, $pdf->GetY(), $pdf->font_size);
                            if ($len_y == $pdf->font_size) {
                                $pdf->Cell($len_x, $len_y, $pdf->getPrintableStr((string) $key, $len_x, $len_y), 0);

                                $pdf->SetTextColor(111, 106, 106);
                            } else {
                                $len_y = 0;
                            }
                            $pdf->SetFont('Arial', '', $pdf->font_size);
                            $label_text_width = $pdf->GetStringWidth($value . "1234");
                            if ($len_y != 0)
                                $pdf->SetXY($x + $left_space + $pdf->getProportionalX(15) + $label_width, $y + $k);

                            $len_x = $pdf->get_remain_x($x, $pdf->GetX(), $label_text_width);
                            $len_y = $pdf->get_remain_y($y, $pdf->GetY(), $pdf->font_size);
                            if ($len_y == $pdf->font_size) {
                                $pdf->Cell($len_x, $len_y, $pdf->getPrintableStr((string) $value, $len_x, $len_y), 0, 0, 'L');

                                $pdf->SetTextColor(0, 0, 0);
                            }

                            $k = $k + $inc;
                        }
                    }

                    $x+=$labelW + $pdf->hr_space;
                }
                $y+= $labelH + $pdf->vr_space;
            }
        }

        $file_download = "temp/preview_template_".date("Ymd_His").".pdf";
        $pdf->Output($file_download, "f");
        return $file_download;
        exit();
    }

}
