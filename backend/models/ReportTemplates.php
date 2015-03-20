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
class ReportTemplates extends \yii\db\ActiveRecord
{
    const STATUS_DELETED = 2;
    const STATUS_ACTIVE = 1;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'report_templates';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['template_name', 'content'], 'required'],
            [['company_id', 'page_width', 'page_height', 'top_margin', 'bottom_margin', 'right_margin', 'left_margin', 'qr_code_width', 'bar_code_width', 'project_logo_width', 'project_image_width'], 'integer'],
            [['template_name'], 'string', 'max' => 50],
            ['company_id', 'default', 'value' => \yii::$app->user->identity->company_id],
            ['created_by', 'default', 'value' => \yii::$app->user->identity->id],
            ['created_date', 'default', 'value' => date("Y-m-d")],
            [['page_number_prefix', 'page_number_suffix'], 'safe']
        ];
    }
    
    // default scope to check company_id
    public static function find()
    {
        $query = parent::find()->where(['company_id' => \yii::$app->user->identity->company_id])->andWhere(['status' => '1']);
        return $query;
    }
   
    public function fields() {
        
        $post = \Yii::$app->request->post();
        
        if(isset($post['ReportTemplates']['select']))
           return $post['ReportTemplates']['select'];
        
        return [
            'id',
            'template_name',
            'content',
            'company_id',
            'page_width',
            'page_height',
            'top_margin',
            'bottom_margin',
            'right_margin',
            'left_margin',
            'qr_code_width',
            'bar_code_width',
            'project_logo_width',
            'project_image_width',
            'page_number_prefix',
            'page_number_suffix'
        ];
    }
        
    public function actDelete() {
        $this->status = 2;
        return $this->save();
    }
}
