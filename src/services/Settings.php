<?php
/**
 * EnupalSlider plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2017 Enupal
 */

namespace enupal\slider\services;

use Craft;
use yii\base\Component;

use enupal\slider\Slider;
use enupal\slider\models\Settings as SettingsModel;

class Settings extends Component
{

    /**
     * @param $settings SettingsModel
     * @return bool
     */
    public function saveSettings($settings): bool
    {
        $plugin = $this->getPlugin();

        $success = Craft::$app->getPlugins()->savePluginSettings($plugin, $settings->getAttributes());

        return $success;
    }

    /**
     * @return \craft\base\PluginInterface|null
     */
    public function getPlugin()
    {
        return Craft::$app->getPlugins()->getPlugin('enupal-slider');
    }

    /**
     * @return SettingsModel|null
     */
    public function getSettings()
    {
        return $this->getPlugin()->getSettings();
    }
}
