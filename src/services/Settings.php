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

class Settings extends Component
{

    /**
     * Saves Settings
     *
     * @param array $postSettings
     *
     * @return bool
     */
    public function saveSettings($postSettings): bool
    {
        $settings = Slider::$app->sliders->getSettings();

        if (isset($postSettings['pluginNameOverride'])) {
            $settings['pluginNameOverride'] = $postSettings['pluginNameOverride'];
        }

        $settings = json_encode($settings);

        $affectedRows = Craft::$app->getDb()->createCommand()->update('{{%plugins}}', [
            'settings' => $settings
        ],
            [
                'handle' => 'enupal-slider'
            ]
        )->execute();

        return (bool)$affectedRows;
    }
}
