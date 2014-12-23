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
		'js/app.js',
		'js/module.js',
                'js/bootstrap-submenu.js',
                'js/html5shiv.js',
                'js/respond.js',
                'js/site.js',
	];
	public $depends = [
		'yii\web\YiiAsset',
		'yii\bootstrap\BootstrapAsset',
		'backend\assets\AngularAsset',
		'backend\assets\AngularUiAsset',
	];
}

