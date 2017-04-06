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

	/**
	 * Returns a Slider model if one is found in the database by id
	 *
	 * @param int $sliderId
	 * @param int $siteId
	 *
	 * @return null|SliderElement
	 */
	public function getSliderById(int $sliderId, int $siteId = null)
	{
		$query = SliderElement::find();
		$query->id($sliderId);
		$query->siteId($siteId);
		// @todo - research next function
		#$query->enabledForSite(false);

		return $query->one();
	}

}
