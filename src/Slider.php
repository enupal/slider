<?php
/**
 * EnupalSlider plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2017 Enupal
 */

namespace enupal\slider;

use Craft;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\services\Fields;
use craft\web\UrlManager;
use enupal\slider\fields\Sliders;
use yii\base\Event;
use craft\web\twig\variables\CraftVariable;
use enupal\slider\services\App;

use enupal\slider\variables\SliderVariable;
use enupal\slider\models\Settings;

class Slider extends \craft\base\Plugin
{
    /**
     * Enable use of Slider::$app-> in place of Craft::$app->
     *
     * @var App
     */
    public static $app;

    public $hasCpSection = true;
    public $hasCpSettings = true;
    public $schemaVersion = '1.3.0';

    public function init()
    {
        parent::init();
        self::$app = $this->get('app');

        $settings = $this->getSettings();

        if ($settings->pluginNameOverride) {
            $this->name = $settings->pluginNameOverride;
        }

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
                $event->rules = array_merge($event->rules, $this->getCpUrlRules());
            }
        );

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function(Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('enupalslider', SliderVariable::class);
            }
        );

        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = Sliders::class;
        });
    }

    protected function afterInstall()
    {
        self::$app->sliders->installDefaultVolume();
    }

    /**
     * Performs actions after the plugin is installed.
     */
    protected function afterUninstall()
    {
        self::$app->sliders->removeVolumeAndFields();
    }

    protected function createSettingsModel()
    {
        return new Settings();
    }

    public function getCpNavItem()
    {
        $parent = parent::getCpNavItem();
        return array_merge($parent, [
            'subnav' => [
                'sliders' => [
                    "label" => Slider::t("Sliders"),
                    "url" => 'enupal-slider/sliders'
                ],
                'settings' => [
                    "label" => Slider::t("Settings"),
                    "url" => 'enupal-slider/settings'
                ]
            ]
        ]);
    }

    /**
     * Settings HTML
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    protected function settingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('enupal-slider/settings/index');
    }

    /**
     * @param string $message
     * @param array  $params
     *
     * @return string
     */
    public static function t($message, array $params = [])
    {
        return Craft::t('enupal-slider', $message, $params);
    }

    public static function log($message, $type = 'info')
    {
        Craft::$type(self::t($message), __METHOD__);
    }

    public static function info($message)
    {
        Craft::info(self::t($message), __METHOD__);
    }

    public static function error($message)
    {
        Craft::error(self::t($message), __METHOD__);
    }

    /**
     * @return array
     */
    private function getCpUrlRules()
    {
        return [
            'enupal-slider/slider/new' =>
                'enupal-slider/sliders/edit-slider',

            'enupal-slider/slider/edit/<sliderId:\d+>' =>
                'enupal-slider/sliders/edit-slider',

            'enupal-slider/settings' =>
                'enupal-slider/settings',
        ];
    }
}

