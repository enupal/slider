<?php
/**
 * EnupalSlider plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2017 Enupal
 */

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
			'vendor/jquery.easing.1.3.js',
			'jquery.bxslider.min.js',
			'initialize.js',
			'vendor/jquery.fitvids.js',
		];

		$this->css = [
			'jquery.bxslider.min.css'
		];

		parent::init();
	}
}