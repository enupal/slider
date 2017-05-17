<?php
namespace enupal\slider\assetbundles;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class SliderAsset extends AssetBundle
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
			'initialize.js',
			'jquery.bxslider.min.js'
		];

		$this->css = [
			'jquery.bxslider.min.css'
		];

		parent::init();
	}
}