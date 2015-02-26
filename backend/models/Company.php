<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "company".
 *
 * @property integer $id
 * @property string $company_name
 * @property integer $company_owner
 * @property string $company_logo
 * @property integer $company_status
 * @property string $expiry_date
 * @property string $created_date
 */
class Company extends \yii\db\ActiveRecord
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    /**
     * @inheritdoc
     */
    
    public $temp_path;


    public static function tableName()
    {
        return 'company';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_name', 'company_owner', 'company_logo', 'company_status', 'expiry_date', 'membership_id'], 'required'],
            [['company_status'], 'integer'],
            ['company_status', 'default', 'value' => self::STATUS_ACTIVE],
            [['expiry_date', 'created_date'], 'safe'],
            [['company_name'], 'string', 'max' => 256],
            [['company_logo'], 'string', 'max' => 128]
        ];
    }
    
    public function afterSave($insert, $changedAttributes) {
        
        if($this->temp_path) {
            $fileManager = new FileManager($this->id);
            $projectPath = $fileManager->getPath("")."/";

            if(file_exists("temp/".$this->company_logo))
                rename("temp/".$this->company_logo, $projectPath.$this->company_logo);
            
            if(isset($changedAttributes['company_logo']) && $changedAttributes['company_logo'] && file_exists($projectPath.$changedAttributes['company_logo']))
                unlink($projectPath.$changedAttributes['company_logo']);
        }
        
        if(isset($changedAttributes['membership_id'])){
            $model = new CompanyMembershipLogs();
            $data = ['company_id'=> $this->id,'membership_id'=>$this->membership_id];
            $model->setAttributes($data);
            $model->save(false);
        }
        
        parent::afterSave($insert, $changedAttributes);
    }

    public function fields() {
        return [
            'id',
            'company_name',
            'company_owner',
            'company_logo' => function() {
                if($this->company_logo)
                    return "filemanager/getimage?company=".$this->id."&type=&file=".$this->company_logo;
            },
            'membership_id',
            'company_status',
            'expiry_date' => function() {
                if(strtotime($this->expiry_date))
                    return date("d M Y", strtotime($this->expiry_date));
            }
        ];
    }
    
    public function extraFields() {
        return [
            'membership',
            'user' => function() {
                $role = Roles::find()->where(['company_id' => $this->id, 'role_name' => 'Super Admin'])
                                ->one()->id;
                
                return User::find()->where(['company_id' => $this->id])->andWhere(['role' => $role])->one();
            },
            'stats' => function() {
                $return = array();
                $return['projects']['count'] = Projects::find()->where(['company_id' => $this->id])->count();
                $return['tags']['count'] = Tags::find()->where(['company_id' => $this->id])->count();
                $return['users']['count'] = User::find()->where(['company_id' => $this->id])->andWhere(['user.status' => 1])->count();
                $return['items']['count'] = Items::find()->where(['company_id' => $this->id])->count();
                
                $fileManager = new \backend\models\FileManager($this->id);

                $rootPath = $fileManager->getRootPath();

                if(!file_exists($rootPath))
                    mkdir ($rootPath);
                
                $return['data']['count'] = $this->getFolderSize($rootPath);
                
                return $return;
            }
        ];
    }
    
    public static function find() {
        return parent::find()->andWhere(['<>', 'company_status', 2]);
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_name' => 'Company Name',
            'company_owner' => 'Company Owner',
            'company_logo' => 'Company Logo',
            'company_status' => 'Company Status',
            'expiry_date' => 'Expiry Date',
            'created_date' => 'Created Date',
        ];
    }
    
    public function actDelete() {
        $this->company_status = 2;
        return $this->save();
    }
    
    public function getMembership()
    {
        return $this->hasOne(Membership::className(), ['id' => 'membership_id']);
    }
    
    public function getFolderSize($path) {
        
        $path = realpath($path);
        
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $obj = new \COM ( 'scripting.filesystemobject' );
                        
            if ( is_object ( $obj ) )
            {
                $ref = $obj->getfolder ( $path );
                $size = $ref->size;
                $obj = null;
            }
            else
            {
                echo 'can not create object';
            }
        } else {
            $io = popen ( '/usr/bin/du -sk ' . $path, 'r' );
            $size = fgets ( $io, 4096);
            $size = substr ( $size, 0, strpos ( $size, "\t" ) );
            pclose ( $io );
        }
        return $this->format_size($size);
    }
    
    public function format_size($size, $currentSize="") {
        $units = explode(' ', 'B KB MB GB TB PB');
        
        $mod = 1024;

        for ($i = 0; $size > $mod; $i++) {
            $size /= $mod;
        }

        $endIndex = strpos($size, ".")+3;
        
        if($currentSize) {
            $i += array_search($currentSize, $units);
        }

        return substr( $size, 0, $endIndex).' '.$units[$i];
    }
        
}