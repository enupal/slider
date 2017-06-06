<?php
namespace enupal\slider\assetbundles;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class LivePreviewAsset extends AssetBundle
{
	public function init()
	{
		// define the path that your publishable resources live
		$this->sourcePath = '@enupal/slider/resources/';

		// define the dependencies
		// define the relative path to CSS/JS files that should be registered with the page
		// when this asset bundle is registered
		$this->js = [
			'preview/boostrap.min.js'
		];

		$this->css = [
			'preview/boostrap.min.css',
		];

		parent::init();
	}
}