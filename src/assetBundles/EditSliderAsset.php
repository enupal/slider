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

class EditSliderAsset extends AssetBundle
{
	public function init()
	{
		// define the path that your publishable resources live
		$this->sourcePath = '@enupal/slider/resources/';

		// define the dependencies

		// define the relative path to CSS/JS files that should be registered with the page
		// when this asset bundle is registered
		$this->js = [
			'clipboard/clipboard.min.js',
			'js/enupalslider.js'
		];

		$this->css = [
			'css/enupalslider.css',
			'css/font/css/font-awesome.min.css'
		];

		parent::init();
	}
}