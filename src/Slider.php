<?php
namespace enupal\slider;

use Craft;

use enupal\variables\SliderVariable;

class Slider extends \craft\base\Plugin
{
	/**
	 * Enable use of Slider::$plugin-> in place of Craft::$app->
	 *
	 * @var [type]
	 */
	public static $api;

	public $hasCpSection = true;

	public function init()
	{
		parent::init();

		self::$api = $this->get('api');

		Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
				$event->rules = array_merge($event->rules, $this->getCpUrlRules());
			}
		);

		Event::on(Fields::class, Fields::EVENT_REGISTER_FIELDS, function(RegisterFieldsEvent $event) {
				$event->fields[] = new PlainText();
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

