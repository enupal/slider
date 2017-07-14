<?php
namespace enupal\slider;

use Craft;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use yii\base\Event;
use craft\events\DefineComponentsEvent;
use craft\web\twig\variables\CraftVariable;

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

		Event::on(
			CraftVariable::class,
			CraftVariable::EVENT_DEFINE_COMPONENTS,
			function (DefineComponentsEvent $event) {
					$event->components['enupalslider'] = SliderVariable::class;
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
					"url"   => 'enupal-slider/sliders'
				],
				'settings' =>[
					"label" => Slider::t("Settings"),
					"url" => 'enupal-slider/settings'
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
			'enupal-slider'            =>
			'enupal-slider/sliders/index',

			'enupal-slider/slider/new'            =>
			'enupal-slider/sliders/edit-slider',

			'enupal-slider/slider/edit/<sliderId:\d+>'            =>
			'enupal-slider/sliders/edit-slider',

			'enupal-slider/settings'            =>
			'enupal-slider/settings',
		];
	}
}

