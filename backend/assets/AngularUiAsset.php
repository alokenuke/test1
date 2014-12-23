<?php
namespace backend\assets;
use yii\web\AssetBundle;
/**
 * Class AngularAsset
 * @package yii\web
 */
class AngularUiAsset extends AssetBundle
{
	public $sourcePath = '@vendor/angular-ui/bootstrap';

	/**
	 * @param \yii\web\View $view
	 */
	public function registerAssetFiles($view)
	{
		$prefix = !YII_DEBUG ? '.min.' : '.min.';
		$this->js[] = 'ui-bootstrap'.$prefix.'js';
		$this->js[] = 'ui-bootstrap-tpls-0.12.0'.$prefix.'js';
                $this->js[] = 'ui-select'.$prefix.'js';

		parent::registerAssetFiles($view);
	}
}
