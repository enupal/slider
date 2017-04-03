<?php

namespace enupal\slider\variables;

use Craft;

use enupal\slider\Slider;
use enupal\slider\models\Settings;

/**
 * EnupalSlider provides an API for accessing information about sliders. It is accessible from templates via `craft.enupalslider`.
 *
 */
class SliderVariable
{

	/**
	 * @return string
	 */
	public function getName()
	{
		$plugin = Craft::$app->plugins->getPlugin('enupalslider');

		return $plugin->getName();
	}

	/**
	 * @return string
	 */
	public function getVersion()
	{
		$plugin = Craft::$app->plugins->getPlugin('enupalslider');

		return $plugin->getVersion();
	}
}

