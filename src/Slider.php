<?php
namespace enupal\slider;

use Craft;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use yii\base\Event;

use enupal\slider\variables\SliderVariable;
use enupal\slider\models\Settings;

class Slider extends \craft\base\Plugin
{
	/**
	 * Enable use of Slider::$app-> in place of Craft::$app->
	 *
	 * @var [type]
	 */
	public static $app;

	public $hasCpSection = true;

	public function init()
	{
		parent::init();
		self::$app = $this->get('app');

		$settings = Slider::$app->sliders->getSettings();

		if (isset($settings['pluginNameOverride']) && $settings['pluginNameOverride'])
		{
			$this->name = $settings['pluginNameOverride'];
		}

		Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
				$event->rules = array_merge($event->rules, $this->getCpUrlRules());
			}
		);
	}

	protected function afterInstall()
	{
		self::$app->sliders->installDefaultVolume();
	}

	protected function createSettingsModel()
	{
		return new Settings();
	}

	public function getCpNavItem()
	{
		$parent = parent::getCpNavItem();
		return array_merge($parent,[
			'subnav' => [
				'sliders' => [
					"label" => Slider::t("Sliders"),
					"url"   => 'enupalslider/sliders'
				],
				'settings' =>[
					"label" => Slider::t("Settings"),
					"url" => 'enupalslider/settings'
				]
			]
		]);
	}

	/**
	 * @param string $message
	 * @param array  $params
	 *
	 * @return string
	 */
	public static function t($message, array $params = [])
	{
		return Craft::t('enupalslider', $message, $params);
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
			'enupalslider'            =>
			'enupalslider/sliders/index',

			'enupalslider/slider/new'            =>
			'enupalslider/sliders/edit-slider',

			'enupalslider/slider/edit/<sliderId:\d+>'            =>
			'enupalslider/sliders/edit-slider',
		];
	}

	/**
	 * @return string
	 */
	public function defineTemplateComponent()
	{
		return SliderVariable::class;
	}
}

