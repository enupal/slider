<?php

namespace enupal\slider\variables;

use Craft;
use craft\helpers\Template as TemplateHelper;
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

	/**
	 * @return mixed
	 */
	public function getModes()
	{
		$options = [
			'horizontal' => 'Horizontal',
			'vertical'   => 'Vertical',
			'fade'       => 'Fade',
		];

		return $options;
	}

	/**
	 * @return mixed
	 */
	public function getEasingOptions()
	{
		$options = [
			'linear'      => 'Linear',
			'ease'        => 'Ease',
			'ease-in'     => 'Ease-in',
			'ease-out'    => 'Ease-out',
			'ease-in-out' => 'Ease-in-out',
		];

		return $options;
	}

	/**
	 * @return mixed
	 */
	public function getPreloadImagesOptions()
	{
		$options = [
			'all'     => 'All',
			'visible' => 'Visible',
		];

		return $options;
	}

	/**
	 * Returns a complete enupal slider for display in template
	 *
	 * @param string     $sliderHandle
	 * @param array|null $options
	 *
	 * @return string
	 */
	public function displaySlider($sliderHandle, array $options = null)
	{
		$slider         = Slider::$app->sliders->getSliderByHandle($sliderHandle);
		$templatePath   = Slider::$app->sliders->getEnupalSliderPath();
		$slidesElements = [];
		$sliderHtml     = null;
		$settings       = Slider::$app->sliders->getSettings();

		if ($slider)
		{
			$dataAttributes = Slider::$app->sliders->getDataAttributes($slider);
			$slides = json_decode($slider->slides);

			foreach ($slides as $key => $slideId)
			{
				$slide = Craft::$app->elements->getElementById($slideId);
				array_push($slidesElements, $slide);
			}

			$view = Craft::$app->getView();

			$view->setTemplatesPath($templatePath);

			$sliderHtml = $view->renderTemplate(
				'slider', [
					'slider'         => $slider,
					'slidesElements' => $slidesElements,
					'dataAttributes' => $dataAttributes,
					'htmlHandle'     => $settings['htmlHandle'],
					'sourceHandle'   => $settings['sourceHandle'],
					'options'        => $options
				]
			);

			$view->setTemplatesPath(Craft::$app->path->getSiteTemplatesPath());
		}
		else
		{
			$sliderHtml = Slider::t("Slider {$sliderHandle} not found");
		}

		return TemplateHelper::raw($sliderHtml);
	}
}

