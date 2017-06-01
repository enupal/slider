<?php
namespace enupal\slider\assetbundles;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class LivePreviewAsset extends AssetBundle
{
	public function init()
	{
		// define the path that your publishable resources live
		$this->sourcePath = '@enupal/slider/resources/bxslider-4/';

		// define the dependencies
		// define the relative path to CSS/JS files that should be registered with the page
		// when this asset bundle is registered
		$this->js = [
			'jquery.min.js',
			'vendor/jquery.easing.1.3.js',
			'jquery.bxslider.min.js',
			'vendor/jquery.fitvids.js',
		];

		$this->css = [
			'jquery.bxslider.min.css'
		];

		parent::init();
	}
}