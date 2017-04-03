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

		Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
				$event->rules = array_merge($event->rules, $this->getCpUrlRules());
			}
		);
	}

	protected function createSettingsModel()
	{
		return new Settings();
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

	/**
	 * @return array
	 */
	private function getCpUrlRules()
	{
		return [
			'enupalslider/all'            =>
			'enupal-slider/slider/index',
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

