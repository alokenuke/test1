<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of printLabel
 *
 * @author himanshu.maheshwari
 */
namespace backend\models\pdf;

class printLabel extends FPDF {

    var $l_margin;
    var $t_margin;
    var $r_margin;
    var $b_margin;
    var $hr_labels;
    var $vr_labels;
    var $hr_space;
    var $vr_space;
    var $pgWidth;
    var $pgHeight;
    var $type;
    var $labelW;
    var $labelH;
    var $font_size;

    function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $data) {
        //Call parent constructor
        $this->FPDF($orientation, $unit, $format);
        //Initialization
        $this->B = 0;
        $this->I = 0;
        $this->U = 0;
        $this->HREF = '';
        $this->fontlist = array('arial', 'times', 'courier', 'helvetica', 'symbol');
        $this->issetfont = false;
        $this->issetcolor = false;
        $this->l_margin = empty($data['leftMargin']) ? 0 : $data['leftMargin'];
        $this->t_margin = empty($data['topMargin']) ? 0 : $data['topMargin'];
        $this->r_margin = empty($data['rightMargin']) ? 0 : $data['rightMargin'];
        $this->b_margin = empty($data['bottomMargin']) ? 0 : $data['bottomMargin'];
        $this->hr_labels = $data['numberHorizontal'];
        $this->vr_labels = $data['numberVertical'];
        $this->hr_space = $data['hSpace'];
        $this->vr_space = $data['vSpace'];
        $this->pgWidth = $data['pageWidth'];
        $this->pgHeight = $data['pageHeight'];
        $this->type = $data['type'];
        $this->font_size = $data['font_size'];
    }

    function getPrintableStr($str, $size, $len_y = 100) {

        try {
            if ($len_y == 0 || $str == NULL)
                return('');
            $len = $this->GetStringWidth($str);
            if ($len == 0)
                return($len);
            $one_char_req_space_mm = $len / strlen($str);
          
            $printable_len = $size / $one_char_req_space_mm;
            
            return(substr($str, 0, $printable_len<=0?0:$printable_len));
        } catch (Exception $e) {
            var_dump($e);
        }
    }

    function get_remain_x($ini_x, $cur_x, $req) {
        $max_x = $ini_x + $this->labelW;
        $req_x = $req + $cur_x;

        $avail_x = $max_x - $req_x;
        if ($avail_x <= 0) {
            if ($req + $avail_x > 0)
                return($req + $avail_x - 1);
            else
                return(0);
        }
        else
            return($req);
    }

    function get_remain_y($ini_y, $cur_y, $req) {
        $max_y = $ini_y + $this->labelH;
        $req_y = $req + $cur_y;

        $avail_y = $max_y - $req_y;
        if ($avail_y <= 0) {
            if ($req + $avail_y > 0)
                return($req + $avail_y);
            else
                return(0);
        }
        else
            return($req);
    }

    function calculatedLabelW() {

        if ($this->hr_labels != 0 && $this->vr_labels != 0)
            $this->labelW = ($this->pgWidth - $this->l_margin - $this->r_margin - ($this->hr_labels - 1) * $this->hr_space) / $this->hr_labels;
        else
            $this->labelW = 0;

        return($this->labelW);
    }

    function calculatedLabelH() {
        if ($this->hr_labels != 0 && $this->vr_labels != 0)
            $this->labelH = ($this->pgHeight - $this->t_margin - $this->b_margin - ($this->vr_labels - 1) * $this->vr_space) / $this->vr_labels;
        else
            $this->labelH = 0;

        return($this->labelH);
    }

    function getProportionalX($param) {
//        $param=($param*50)/100;
        return(($param * $this->labelW) / 100);
    }

    function getProportionalY($param) {
//        $param=($param*40)/100;
        return(($param * $this->labelH) / 100);
    }

    function setapprFontSize($size,$str,$font_size) {
        $x = $font_size;    // Will hold the font size
        /* I will cycle decreasing the font size until it's width is lower than the max width */
        while ($this->GetStringWidth(utf8_decode($str)) > $size) {
            $x--;   // Decrease the variable which holds the font size
            $this->SetFont('Arial', 'B', $x);  // Set the new font size
        }
        /* Output the string at the required font size */
        //$pdf->Cell( 116, 7, utf8_decode( $row_or['client_name'] ) ), 0, 0, 'L' );
        /* Return the font size to itś original */
        $this->SetFont('Arial', 'B',$x);
        return($x);
    }
}

?>
