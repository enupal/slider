<?php
namespace enupal\slider\services;

use Craft;
use craft\base\Component;
use enupal\slider\Slider;

class App extends Component
{
	public $sliders;

	public function init()
	{
		$this->sliders = new Sliders();
	}
}