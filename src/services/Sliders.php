<?php
namespace enupal\slider\services;

use Craft;
use yii\base\Component;

use enupal\slider\Slider;
use enupal\slider\elements\Slider as SliderElement;
use enupal\slider\records\Slider as SliderRecord;

class Sliders extends Component
{
	public $activeEntries;
	public $activeCpEntry;

	protected $sliderRecord;

	/**
	 * Constructor
	 *
	 * @param object $sliderRecord
	 */
	public function __construct($sliderRecord = null)
	{
		$this->sliderRecord = $sliderRecord;

		if (is_null($this->sliderRecord))
		{
			$this->sliderRecord = new SliderRecord();
		}
	}

}
