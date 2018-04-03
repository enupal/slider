<?php
/**
 * EnupalSlider plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2017 Enupal
 */

namespace enupal\slider\variables;

use Craft;
use craft\helpers\Template as TemplateHelper;
use enupal\slider\Slider;

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
        $plugin = Craft::$app->plugins->getPlugin('enupal-slider');

        return $plugin->getName();
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        $plugin = Craft::$app->plugins->getPlugin('enupal-slider');

        return $plugin->getVersion();
    }

    /**
     * @return mixed
     */
    public function getModes()
    {
        $options = [
            'horizontal' => 'Horizontal',
            'vertical' => 'Vertical',
            'fade' => 'Fade',
        ];

        return $options;
    }

    /**
     * @param bool $useCss
     *
     * @return mixed
     */
    public function getEasingOptions($useCss = true)
    {
        $options = [
            'linear' => 'Linear',
            'ease' => 'Ease',
            'ease-in' => 'Ease-in',
            'ease-out' => 'Ease-out',
            'ease-in-out' => 'Ease-in-out',
        ];

        if (!$useCss) {
            $options = [
                'swing' => 'swing',
                'easeInQuad' => 'easeInQuad',
                'easeOutQuad' => 'easeOutQuad',
                'easeInOutQuad' => 'easeInOutQuad',
                'easeInCubic' => 'easeInCubic',
                'easeOutCubic' => 'easeOutCubic',
                'easeInOutCubic' => 'easeInOutCubic',
                'easeInQuart' => 'easeInQuart',
                'easeOutQuart' => 'easeOutQuart',
                'easeInOutQuart' => 'easeInOutQuart',
                'easeInQuint' => 'easeInQuint',
                'easeOutQuint' => 'easeOutQuint',
                'easeInOutQuint' => 'easeInOutQuint',
                'easeInSine' => 'easeInSine',
                'easeOutSine' => 'easeOutSine',
                'easeInOutSine' => 'easeInOutSine',
                'easeInExpo' => 'easeInExpo',
                'easeOutExpo' => 'easeOutExpo',
                'easeInOutExpo' => 'easeInOutExpo',
                'easeInCirc' => 'easeInCirc',
                'easeOutCirc' => 'easeOutCirc',
                'easeInOutCirc' => 'easeInOutCirc',
                'easeInElastic' => 'easeInElastic',
                'easeOutElastic' => 'easeOutElastic',
                'easeInOutElastic' => 'easeInOutElastic',
                'easeInBack' => 'easeInBack',
                'easeOutBack' => 'easeOutBack',
                'easeInOutBack' => 'easeInOutBack',
                'easeInBounce' => 'easeInBounce',
                'easeOutBounce' => 'easeOutBounce',
                'easeInOutBounce' => 'easeInOutBounce'
            ];
        }

        return $options;
    }

    /**
     * @return mixed
     */
    public function getPreloadImagesOptions()
    {
        $options = [
            'all' => 'All',
            'visible' => 'Visible',
        ];

        return $options;
    }

    /**
     * @return mixed
     */
    public function getPagerTypeOptions()
    {
        $options = [
            'full' => 'All',
            'short' => 'Short',
        ];

        return $options;
    }

    /**
     * @return mixed
     */
    public function getAutoDirectionOptions()
    {
        $options = [
            'next' => 'Next',
            'prev' => 'Prev',
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
        return Slider::$app->sliders->getSliderHtml($sliderHandle, $options);
    }

    /**
     * @return string
     */
    public function getResourcesPath()
    {
        return Craft::$app->path->getPluginsPath().'/enupalslider/src/resources/';
    }

    /**
     * @return string
     */
    public function getSettings()
    {
        return Slider::$app->sliders->getSettings();
    }

    /**
     * @return array
     */
    public function getTransforms()
    {
        $options = [
            '' => Slider::t('None')
        ];

        $transforms = Craft::$app->assetTransforms->getAllTransforms();

        if (count($transforms)) {
            foreach ($transforms as $transform) {
                $options[$transform->handle] = $transform->name;
            }
        }

        return $options;
    }

    /**
     * Gets a specific slider.
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function getSliderById($id)
    {
        return Slider::$app->sliders->getSliderById($id);
    }

    /**
     * Gets a specific slider by handle.
     *
     * @param  string $handle
     *
     * @return mixed
     */
    public function getSlider($handle)
    {
        return Slider::$app->sliders->getSliderByHandle($handle);
    }

}

