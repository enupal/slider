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
use craft\helpers\FileHelper;
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
        $slider = Slider::$app->sliders->getSliderByHandle($sliderHandle);
        $templatePath = Slider::$app->sliders->getEnupalSliderPath();
        $slidesElements = [];
        $sliderHtml = null;
        $settings = Slider::$app->sliders->getSettings();

        if ($slider) {
            $dataAttributes = Slider::$app->sliders->getDataAttributes($slider);
            $slides = json_decode($slider->slides);

            foreach ($slides as $key => $slideId) {
                $slide = Craft::$app->elements->getElementById($slideId);
                array_push($slidesElements, $slide);
            }

            $view = Craft::$app->getView();

            $view->setTemplatesPath($templatePath);

            $sliderHtml = $view->renderTemplate(
                'slider', [
                    'slider' => $slider,
                    'slidesElements' => $slidesElements,
                    'dataAttributes' => $dataAttributes,
                    'htmlHandle' => $settings['htmlHandle'],
                    'linkHandle' => $settings['linkHandle'],
                    'openLinkHandle' => $settings['openLinkHandle'],
                    'options' => $options
                ]
            );

            $view->setTemplatesPath(Craft::$app->path->getSiteTemplatesPath());
        } else {
            $sliderHtml = Slider::t("Slider {$sliderHandle} not found");
        }

        return TemplateHelper::raw($sliderHtml);
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

}

