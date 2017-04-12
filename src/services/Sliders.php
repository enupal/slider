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

	/**
	 * @param SliderElement $slider
	 *
	 * @throws \Exception
	 * @return bool
	 */
	public function saveSlider(SliderElement $slider)
	{
		$isNewSlider  = true;

		if ($slider->id)
		{
			$sliderRecord = SliderRecord::findOne($slider->id);

			if (!$sliderRecord)
			{
				throw new Exception(Slider::t('No Slider exists with the ID “{id}”', ['id' => $slider->id]));
			}
		}

		$slider->validate();

		if (!$slider->hasErrors())
		{
			$transaction = Craft::$app->db->getTransaction() === null ? Craft::$app->db->beginTransaction() : null;
			try
			{
				if (Craft::$app->elements->saveElement($slider, false))
				{
					if ($transaction !== null)
					{
						$transaction->commit();
					}

					return true;
				}
			}
			catch (\Exception $e)
			{
				if ($transaction !== null)
				{
					$transaction->rollback();
				}

				throw $e;
			}
		}
	}

}
