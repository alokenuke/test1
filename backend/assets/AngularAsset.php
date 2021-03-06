<?php
namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Class AngularAsset
 * @package yii\web
 */
class AngularAsset extends AssetBundle
{
	public $language;
	public $sourcePath = '@vendor/components/angular.js';
	public $css = [
		'angular-csp.css',
	];
	/**
	 * @param $view
	 */
	public function registerAssetFiles($view)
	{
		$prefix = !YII_DEBUG ? '.min.' : '.';
		$language = $this->language ? $this->language : 'en';
		$this->js[] = 'angular' . $prefix . 'js';
                $this->js[] = 'fileUpload/angular-file-upload-shim' . $prefix . 'js';
                $this->js[] = 'fileUpload/angular-file-upload' . $prefix . 'js';
		$this->js[] = 'angular-route' . $prefix . 'js';
		$this->js[] = 'angular-touch' . $prefix . 'js';
		$this->js[] = 'angular-animate' . $prefix . 'js';
                $this->js[] = 'angular-resource' . $prefix . 'js';
                $this->js[] = 'angular-sanitize' . $prefix . 'js';
                $this->js[] = 'angular-ui-tree' . $prefix . 'js';
                //$this->css[] = 'angular-ui-tree' . $prefix . 'css';
                
                $this->js[] = 'loading-bar' . $prefix . 'js';
                $this->css[] = 'loading-bar' . $prefix . 'css';
                
                $this->js[] = 'i18n/angular-locale_' . $language . '.js';
		parent::registerAssetFiles($view);
	}
}

