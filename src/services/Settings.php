<?php
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

		if (isset($postSettings['pluginNameOverride']))
		{
			$settings['pluginNameOverride'] = $postSettings['pluginNameOverride'];
		}

		$settings = json_encode($settings);

		$affectedRows = Craft::$app->getDb()->createCommand()->update('plugins', [
			'settings' => $settings
			],
			[
			'handle' => 'enupalslider'
			]
		)->execute();

		return (bool) $affectedRows;
	}
}
