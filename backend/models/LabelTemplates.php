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
            [['template_name', 'company_id', 'print_type', 'logo', 'checked_labels', 'logo_position'], 'required'],
            [['company_id', 'page_width', 'page_height', 'logo_width', 'logo_height', 'font_size', 'top_margin', 'bottom_margin', 'right_margin', 'left_margin', 'num_label_horizontal', 'num_label_vertical', 'hor_label_spacing', 'ver_label_spacing'], 'integer'],
            [['cal_label_width', 'cal_label_height'], 'number'],
            [['template_name'], 'string', 'max' => 50],
            [['additional_notes'], 'string', 'max' => 64],
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
    
    public function beforeSave($insert)
    {
        
        $this->checked_labels = json_encode($this->checked_labels);
        
        if($this->logo) {
            $fileManager = new FileManager();
            
            $temp_file = $this->logo;
            $this->logo = array_pop(preg_split("/((=)|(\/))/", $this->logo));
            
            if(isset($this->logo) && strpos($temp_file,'filemanager/getimage') === false && strpos($temp_file,'temp') !== false) {
                try {
                    $rootPath = $fileManager->getRootPath();
                    rename($temp_file, $rootPath."/".$this->logo);
                }catch(Exception $e) {}
            } 
        }
        
        return parent::beforeSave($insert);
    }
    
    public function afterSave($insert, $changedAttributes) {
        if(isset($changedAttributes['logo']) && $changedAttributes['logo'] != $this->logo) {
            $fileManager = new FileManager();
            $projectPath = $fileManager->getRootPath()."/";
            
            if(file_exists($projectPath.$changedAttributes['logo']))
                unlink($projectPath.$changedAttributes['logo']);
        }
        
        parent::afterSave($insert, $changedAttributes);
    }
    
    public function fields() {
        
        $post = \Yii::$app->request->post();
        
        if(isset($post['LabelTemplates']['select']))
           return $post['LabelTemplates']['select'];
        
        return [
            'id',
            'template_name',
            'print_type',
            'company_id',
            'page_width',
            'page_height',
            'logo_width',
            'logo_height',
            'logo_position',
            'font_size',
            'top_margin',
            'bottom_margin',
            'right_margin',
            'left_margin',
            'num_label_horizontal',
            'num_label_vertical',
            'hor_label_spacing',
            'ver_label_spacing',
            'cal_label_width',
            'cal_label_height',
            'print_type',
            'checked_labels' => function() {
                if($this->checked_labels) {
                    return json_decode($this->checked_labels);
                }
            },
            'logo' => function() {
                if($this->logo) {
                    $fileManager = new FileManager();
                    return '/filemanager/getimage?type=&file='.$this->logo;
                }
            },
            'additional_notes'
        ];
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
