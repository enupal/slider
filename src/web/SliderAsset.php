<?php
/**
 * EnupalSlider plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2017 Enupal
 */

namespace enupal\slider\web;

use craft\web\AssetBundle;
use enupal\slider\Slider;

class SliderAsset extends AssetBundle
{
    public function init()
    {
        $plugin = Slider::$app->settings->getPlugin();
        $settings = $plugin->getSettings();
        // define the path that your publishable resources live
        $this->sourcePath = '@enupal/slider/resources/bxslider-4/';

        // define the dependencies
        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        if ($settings->loadJquery){
            $this->js[] = 'jquery.min.js';
        }

        $this->js[] = 'vendor/jquery.easing.1.3.js';
        $this->js[] = 'jquery.bxslider.min.js';
        $this->js[] = 'initialize.js';
        $this->js[] = 'vendor/jquery.fitvids.js';

        $this->css = [
            'jquery.bxslider.min.css'
        ];

        parent::init();
    }
}