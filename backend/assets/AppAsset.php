<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
//	public $language;
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
        'css/animate.min.css',
        //'css/main.css',
        'css/font-awesome.min.css',
        'css/style.css',
    ];
    public $js = [
        'js/services.js',
        'api/initialize',
        'js/module.js',
        'js/tags.js',
        'js/timeattendance.js',
        'js/user.js',
        'js/projects.js',
        'js/reports.js',
        'js/settings.js',
        //'js/bootstrap-submenu.js',
        'js/html5shiv.js',
        'js/respond.js',
        'js/site.js',
        'https://maps.googleapis.com/maps/api/js?v=3.exp'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
        'backend\assets\AngularAsset',
        'backend\assets\AngularUiAsset',
    ];
    
    public function registerAssetFiles($view)
    {
        if(!\yii::$app->user->isGuest) {
            if(\yii::$app->session->get('user.role_details')->type=='Site') {
                $this->js = [];
                
                $this->js[] = "js/services.js";
                $this->js[] = "js/admin.js";
                $this->js[] = 'js/html5shiv.js';
                $this->js[] = 'js/respond.js';
            }
        }
                    
        parent::registerAssetFiles($view);
    }
    
}

