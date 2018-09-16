<?php
/**
 * EnupalSlider plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2017 Enupal
 */

namespace enupal\slider\services;

use Craft;
use craft\base\Component;
use enupal\slider\Slider;

class App extends Component
{
    /**
     * @var Sliders
     */
    public $sliders;

    /**
     * @var Groups
     */
    public $groups;

    /**
     * @var Settings
     */
    public $settings;

    public function init()
    {
        $this->sliders = new Sliders();
        $this->groups = new Groups();
        $this->settings = new Settings();
    }
}