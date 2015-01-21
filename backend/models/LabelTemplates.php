<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "label_templates".
 *
 * @property integer $Id
 * @property string $template_name
 * @property integer $company_id
 * @property integer $page_width
 * @property integer $page_height
 * @property string $print_type
 * @property integer $logo_width
 * @property integer $logo_height
 * @property integer $font_size
 * @property integer $top_margin
 * @property integer $bottom_margin
 * @property integer $right_margin
 * @property integer $left_margin
 * @property double $cal_label_width
 * @property double $cal_label_height
 * @property integer $num_label_horizontal
 * @property integer $num_label_vertical
 * @property integer $hor_label_spacing
 * @property integer $ver_label_spacing
 * @property string $logo
 */
class LabelTemplates extends \yii\db\ActiveRecord
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 1;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'label_templates';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['template_name', 'company_id', 'print_type', 'logo'], 'required'],
            [['company_id', 'page_width', 'page_height', 'logo_width', 'logo_height', 'font_size', 'top_margin', 'bottom_margin', 'right_margin', 'left_margin', 'num_label_horizontal', 'num_label_vertical', 'hor_label_spacing', 'ver_label_spacing'], 'integer'],
            [['cal_label_width', 'cal_label_height'], 'number'],
            [['template_name'], 'string', 'max' => 50],
            [['print_type', 'logo'], 'string', 'max' => 100],
            ['company_id', 'default', 'value' => \yii::$app->user->identity->company_id],
            ['created_by', 'default', 'value' => \yii::$app->user->identity->id],
            ['created_date', 'default', 'value' => date("Y-m-d")],
        ];
    }
    
    // default scope to check company_id
    public static function find()
    {
        $query = parent::find()->where(['company_id' => \yii::$app->user->identity->company_id])->andWhere(['<>', 'status', '0']);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'Id' => 'ID',
            'template_name' => 'Template Name',
            'company_id' => 'Company ID',
            'page_width' => 'Page Width',
            'page_height' => 'Page Height',
            'print_type' => 'Print Height',
            'logo_width' => 'Qr Code Height',
            'logo_height' => 'Logo Height',
            'font_size' => 'Font Size',
            'top_margin' => 'Top Margin',
            'bottom_margin' => 'Bottom Margin',
            'right_margin' => 'Right Margin',
            'left_margin' => 'Left Margin',
            'cal_label_width' => 'Cal Label Width',
            'cal_label_height' => 'Cal Label Height',
            'num_label_horizontal' => 'Num Lebel Horizontal',
            'num_label_vertical' => 'Num Label Vertical',
            'hor_label_spacing' => 'Hor Label Spacing',
            'ver_label_spacing' => 'Ver Label Spacing',
            'logo' => 'Logo',
        ];
    }
        
    public function actDelete() {
        $this->status = 0;
        return $this->save();
    }
}
