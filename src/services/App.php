<?php
/**
 * @link      https://enupal.com/
 * @copyright Copyright (c) Enupal
 * @license   http://enupal.com/craft-plugins/license
 */

namespace enupal\slider\services;

use Craft;
use craft\base\Component;
use enupal\slider\Slider;

class App extends Component
{
	public $sliders;
	public $groups;
	public $settings;

	public function init()
	{
		$this->sliders = new Sliders();
		$this->groups  = new Groups();
		$this->settings  = new Settings();
	}
}