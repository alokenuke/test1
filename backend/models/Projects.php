<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "projects".
 *
 * @property integer $id
 * @property integer $company_id
 * @property string $project_name
 * @property string $client_project_manager
 * @property string $project_location
 * @property string $project_director
 * @property string $about
 * @property string $consultant
 * @property string $main_contractor
 * @property string $project_manager
 * @property string $project_logo
 * @property string $project_image
 * @property string $project_address
 * @property string $project_city
 * @property string $project_country
 * @property string $client_address
 * @property string $client_city
 * @property string $client_country
 * @property integer $project_status
 * @property integer $created_by
 * @property integer $timezone_id
 * @property string $created_date
 * @property string $modified_date
 *
 * @property Tags[] $tags
 */
class Projects extends \yii\db\ActiveRecord
{
    const STATUS_NOTACTIVE = 0;
    const STATUS_ACTIVE = 1;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'projects';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_name', 'client_project_manager', 'project_location', 'about', 'project_address', 'project_city', 'project_country'], 'required'],
            [['project_status', 'timezone_id'], 'integer'],
            [['about'], 'string', 'max' => 512],
            [['created_date', 'modified_date','client_address','client_city','client_country','project_status','client_name','consultant_project_manager','contractor_project_manager'], 'safe'],
            [['project_name'], 'string', 'max' => 200],
            [['client_project_manager', 'project_location', 'project_director', 'consultant', 'main_contractor', 'project_manager', 'project_logo', 'project_image', 'project_address', 'project_city', 'client_address', 'client_city'], 'string', 'max' => 128],
            ['project_status', 'default', 'value' => self::STATUS_ACTIVE],
            ['company_id', 'default', 'value' => \yii::$app->user->identity->company_id],
            ['created_by', 'default', 'value' => \yii::$app->user->id],
            ['created_date', 'default', 'value' => date("Y-m-d")],
        ];
    }
    
    public static function find()
    {
        $query = parent::find()->andWhere(['company_id' => \yii::$app->user->identity->company_id])->andWhere(['project_status' => 1]);
        
        return $query;
    }
    
    public function beforeSave($insert)
    {
        if($this->project_logo) {
            $fileManager = new FileManager();

            if(isset($this->project_logo) && strpos($this->project_logo,'temp') !== false) {
                $temp_file = $this->project_logo;
                $this->project_logo = array_pop(explode('/',$this->project_logo));
                try {
                    if(file_exists($temp_file))
                        rename($temp_file, $fileManager->getPath("project_image")."/".$this->project_logo);
                }catch(Exception $e) {}
            }
            if(isset($this->project_image) && strpos($this->project_image,'temp') !== false) {
                $temp_file = $this->project_image;
                $this->project_image = array_pop(explode('/',$this->project_image));
                try {
                    if(file_exists($temp_file))
                        rename($temp_file, $fileManager->getPath("project_image")."/".$this->project_image);
                }catch(Exception $e) {}
            }
        }
        
        return parent::beforeSave($insert);
    }
    
    public function afterSave($insert, $changedAttributes) {
                
        if(isset($changedAttributes['project_logo']) || isset($changedAttributes['project_image'])) {
            $fileManager = new FileManager();
            $projectPath = $fileManager->getPath("project_image")."/";
        }
        if(isset($changedAttributes['project_logo'])) {
            if(file_exists($projectPath.$changedAttributes['project_logo']))
                unlink($projectPath.$changedAttributes['project_logo']);
        }
        
        if(isset($changedAttributes['project_image'])) {
            if(file_exists($projectPath.$changedAttributes['project_image']))
                unlink($projectPath.$changedAttributes['project_image']);
        }
        
        parent::afterSave($insert, $changedAttributes);
    }
    
    public function fields()
    {
        $post = \Yii::$app->request->post();
        
        if(isset($post['select']))
           return $post['select'];
        
        return [
            'id',
            'project_name',
            'client_project_manager',
            'project_location',
            'project_director',
            'about',
            'consultant',
            'main_contractor',
            'project_manager',
            'project_logo',
            'project_image',
            'project_address',
            'project_city',
            'project_country',
            'client_address',
            'client_city',
            'client_country',
            'project_status',
            'timezone_id',
            'client_name',
            'consultant_project_manager',
            'contractor_project_manager'
        ];
    }
    
    public function extraFields() {
        return [
            'stats' => function() {
                $return = array();
                $return['sTags']['count'] = Tags::find()->andWhere(['type' => 'sT', 'project_id' => $this->id])->count();
                $return['sTags']['activities'] = TagActivityLog::find()->andWhere(['tag_id' => Tags::find()->select("id")->andWhere(['type' => 'sT', 'project_id' => $this->id])])->count();
                
                $return['mTags']['count'] = Tags::find()->andWhere(['type' => 'mT', 'project_id' => $this->id])->count();
                $return['mTags']['activities'] = TagActivityLog::find()->andWhere(['tag_id' => Tags::find()->select("id")->andWhere(['type' => 'mT', 'project_id' => $this->id])])->count();
                
                $return['levels']['count'] = ProjectLevel::find()->andWhere(['parent_id' => 0, 'project_id' => $this->id])->count();
                $return['sublevels']['count'] = ProjectLevel::find()->andWhere(['parent_id' => ProjectLevel::find()->select("project_level.id")->andWhere(['parent_id' => 0, 'project_id' => $this->id])])->count();
                
                $return['item']['count'] = Items::find()->andWhere(['parent_id' => '0', 'project_id' => $this->id])->count();
                $return['subitem']['count'] = Items::find()->andWhere(['parent_id' => Items::find()->select("id")->andWhere(['parent_id' => '0', 'project_id' => $this->id]), 'project_id' => $this->id])->count();
                
                $return['usergroups']['count'] = UserGroups::find()->andWhere(['project_id' => $this->id])->count();
                $return['users']['count'] = RelUserLevelsUsers::find()->andWhere(['user_group_id' => UserGroups::find()->select("user_groups.id")->andWhere(['project_id' => $this->id])])->count();
                
                return $return;
            },
            'completedTags' => function() {
                return Tags::find()->andWhere(['completed' => '1', 'project_id' => $this->id])->count();
            },
            'totalTags' => function() {
                return Tags::find()->andWhere(['project_id' => $this->id])->count();
            },
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Company ID',
            'project_name' => 'Project Name',
            'client_project_manager' => 'Client Project Manager',
            'project_location' => 'Area',
            'project_director' => 'Project Director',
            'about' => 'Project description',
            'consultant' => 'Consultant',
            'main_contractor' => 'Main Contractor',
            'project_manager' => 'Project Manager',
            'project_logo' => 'Project Logo',
            'project_image' => 'Project Image',
            'project_address' => 'Project Address',
            'project_city' => 'Project City',
            'project_country' => 'Project Country',
            'client_address' => 'Client Address',
            'client_city' => 'Client City',
            'client_country' => 'Client Country',
            'project_status' => 'Project Status',
            'created_by' => 'Created By',
            'timezone_id' => 'Timezone ID',
            'created_date' => 'Created Date',
            'modified_date' => 'Modified Date',
        ];
    }
    
    public function actDelete() {
        $this->project_status = 2;
        return $this->save();
    }
}
