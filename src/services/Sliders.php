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
use craft\fields\RichText;
use craft\fields\PlainText;
use craft\fields\Dropdown;
use craft\elements\Asset;
use craft\volumes\Local;
use craft\base\Field;
use craft\models\FieldGroup;
use yii\db\Query;
use craft\records\Field as FieldRecord;
use craft\records\Volume as VolumeRecord;
use craft\records\VolumeFolder as VolumeFolderRecord;
use enupal\slider\Slider;
use enupal\slider\elements\Slider as SliderElement;
use enupal\slider\records\Slider as SliderRecord;
use craft\helpers\FileHelper;

class Sliders extends Component
{
	public $activeEntries;
	public $activeCpEntry;

	protected $sliderRecord;

	/**
	 * Constructor
	 *
	 * @param object $sliderRecord
	 */
	public function __construct($sliderRecord = null)
	{
		$this->sliderRecord = $sliderRecord;

		if (is_null($this->sliderRecord))
		{
			$this->sliderRecord = new SliderRecord();
		}
	}

	/**
	 * Returns a Slider model if one is found in the database by id
	 *
	 * @param int $sliderId
	 * @param int $siteId
	 *
	 * @return null|SliderElement
	 */
	public function getSliderById(int $sliderId, int $siteId = null)
	{
		$query = SliderElement::find();
		$query->id($sliderId);
		$query->siteId($siteId);
		// @todo - research next function
		#$query->enabledForSite(false);

		return $query->one();
	}

	/**
	 * @param SliderElement $slider
	 *
	 * @throws \Exception
	 * @return bool
	 */
	public function saveSlider(SliderElement $slider)
	{
		$isNewSlider  = true;

		if ($slider->id)
		{
			$sliderRecord = SliderRecord::findOne($slider->id);

			if (!$sliderRecord)
			{
				throw new Exception(Slider::t('No Slider exists with the ID “{id}”', ['id' => $slider->id]));
			}
		}

		$slider->validate();

		if ($slider->hasErrors())
		{
			return false;
		}

		$transaction = Craft::$app->db->beginTransaction();

		try
		{
			if (Craft::$app->elements->saveElement($slider))
			{
				$transaction->commit();
			}
		}
		catch (\Exception $e)
		{
			$transaction->rollback();

			throw $e;
		}

		return true;
	}

	public function installDefaultVolume()
	{
		// Let's create the fields for the Slider Layout
		$fieldsService = Craft::$app->getFields();
		$db            = Craft::$app->getDb();

		$transaction = $db->beginTransaction();
		try
		{
			$fieldGroupId = null;

			$fieldGroup = (new Query())
			->select('*')
			->from(['{{%fieldgroups}}'])
			->where(['name' => 'Enupal Slider'])
			->one();

			if (!isset($fieldGroup['id']))
			{
				$fieldGroup = new FieldGroup();
				$fieldGroup->name = "Enupal Slider";
				Craft::$app->getFields()->saveGroup($fieldGroup);

				$fieldGroupId = $fieldGroup->id;
			}
			else
			{
				$fieldGroupId = $fieldGroup['id'];
			}

			$htmlHandle   = $this->getHandleAsNew("enupalSliderHtml");
			$redactorPath = Craft::getAlias('@enupal/slider/redactor/enupalslider');
			$redactorConfigPath = Craft::$app->path->getConfigPath();
			$redactorConfigPath = FileHelper::normalizePath($redactorConfigPath."/redactor");
			FileHelper::copyDirectory($redactorPath, $redactorConfigPath);

			// SET ENUPAL SLIDER CONTEXT
			Craft::$app->content->fieldContext = "global";

			$richTextSettings = [
				"redactorConfig"=> "EnupalSlider.json",
				"purifierConfig"=>"",
				"cleanupHtml"=>"1",
				"purifyHtml"=>"1",
				"columnType"=>"text",
				"availableVolumes"=>"*",
				"availableTransforms"=>"*"
			];
			$htmlField = $fieldsService->createField([
				'type' => RichText::class,
				'name' => Slider::t('Html'),
				'groupId' => $fieldGroupId,
				'handle' => $htmlHandle,
				'settings' => json_encode($richTextSettings),
				'instructions' => Slider::t('Override your image with custom HTML. Leave it blank to disable'),
				'translationMethod' => Field::TRANSLATION_METHOD_LANGUAGE,
			]);
			// Save our field
			Craft::$app->fields->saveField($htmlField);

			$linkHandle = $this->getHandleAsNew("enupalSliderLink");
			$linkField  = $fieldsService->createField([
				'type' => PlainText::class,
				'name' => Slider::t('Link'),
				'handle' => $linkHandle,
				'groupId' => $fieldGroupId,
				'instructions' => Slider::t('Open Link on same window or new tab'),
				'settings'  => '{"placeholder":"Leave it blank to disable","multiline":"","initialRows":"4","charLimit":"","columnType":"text"}',
				'translationMethod' => Field::TRANSLATION_METHOD_LANGUAGE,
			]);
			// Save our field
			Craft::$app->fields->saveField($linkField);

			$openLinkHandle = $this->getHandleAsNew("enupalSliderOpenLink");
			$openLinkField    = $fieldsService->createField([
				'type' => Dropdown::class,
				'name' => Slider::t('Open Link In'),
				'handle' => $openLinkHandle,
				'groupId' => $fieldGroupId,
				'instructions' => Slider::t('Where should be opened the link?'),
				'settings'  => '{"options":[{"label":"Same window","value":"sameWindow","default":"1"},{"label":"New Tab or Window","value":"newTabOrWindow","default":""}]}',
				'translationMethod' => Field::TRANSLATION_METHOD_LANGUAGE,
			]);
			// Save our field
			Craft::$app->fields->saveField($openLinkField);

			// Create a tab
			$tabName           = Slider::t('Enupal Slider');
			$requiredFields    = array();
			$postedFieldLayout = array();

			// Add our new fields
			if (isset($htmlField) && $htmlField->id != null)
			{
				$postedFieldLayout[$tabName][] = $htmlField->id;
			}

			if (isset($linkField) && $linkField->id != null)
			{
				$postedFieldLayout[$tabName][] = $linkField->id;
			}

			if (isset($openLinkField) && $openLinkField->id != null)
			{
				$postedFieldLayout[$tabName][] = $openLinkField->id;
			}

			// Set the field layout
			$fieldLayout = Craft::$app->fields->assembleLayout($postedFieldLayout, $requiredFields);

			/** @var Volume $volume */
			$volumes = Craft::$app->getVolumes();
			// get the full path of the web/enupalslider folder
			$enupalSliderPath = $this->getSliderPath();
			$volumeSettings   = [
				'path' => $enupalSliderPath
			];

			$createNewVolume = true;
			$volumeHandle    = $this->getHandleAsNew("enupalSlider", true);
			// We need validate if the volume exists(unistall) but nothing is override in the settings
			$volume = null;


			$volume = $volumes->createVolume([
				'id' => null,
				// let's add support for local just for now.
				'type' => Local::class,
				'name' => "Enupal Slider",
				'handle' => $volumeHandle,
				'hasUrls' => true,
				'url' => '/enupalslider/',
				'settings' => json_encode($volumeSettings)
			]);

			// Set the field layout
			$fieldLayout->type = SliderElement::class;
			$volume->setFieldLayout($fieldLayout);
			$volume->validate();
			$errors = $volume->getErrors();

			// save it
			$response = $volumes->saveVolume($volume);

			if (!$response)
			{
				Slider::error('Unable to save the volume');
				return false;
			}

			$settings = [
				'pluginNameOverride' => '',
				'volumeId'           => $volume->id,
				'linkHandle'         => $linkHandle,
				'openLinkHandle'     => $openLinkHandle,
				'htmlHandle'         => $htmlHandle,
			];

			$settings = json_encode($settings);
			$affectedRows = Craft::$app->getDb()->createCommand()->update('plugins', [
				'settings' => $settings
				],
				[
				'handle' => 'enupal-slider'
				]
			)->execute();

			$transaction->commit();
		}
		catch (Exception $e)
		{
			$transaction->rollBack();
			Slider::error('Failed to save element: '.$e->getMessage());
			throw $e;
		}

		return true;
	}

	public function createNewSlider($name = null, $handle = null): SliderElement
	{
		$slider = new SliderElement();
		$name   = empty($name) ? 'Slider' : $name ;
		$handle = empty($handle) ? 'slider' : $handle;

		$slider->name   = $this->getFieldAsNew('name', $name);
		$slider->handle = $this->getFieldAsNew('handle', $handle);
		$slider->slides = [];

		if($this->saveSlider($slider))
		{
			$settings = $this->getSettings();
			$sources  = null;

			if (isset($settings['volumeId']))
			{
				$folder = (new Query())
				->select('*')
				->from(['{{%volumefolders}}'])
				->where(['volumeId' => $settings['volumeId']])
				->one();

				$defaultSubFolder = new VolumeFolderRecord();
				$defaultSubFolder->parentId = $folder['id'];
				$defaultSubFolder->volumeId = $settings['volumeId'];
				$defaultSubFolder->name = $slider->handle;
				$defaultSubFolder->path = $slider->handle."/";
				$defaultSubFolder->save();
			}
		}

		return $slider;
	}

	public function updateSubFolder(SliderElement $slider, string $oldSubfolder): bool
	{
		$settings = $this->getSettings();

		if (isset($settings['volumeId']))
		{
			$folder = (new Query())
			->select('*')
			->from(['{{%volumefolders}}'])
			->where(['volumeId' => $settings['volumeId']])
			->one();

			if ($folder)
			{
				$subFolder = (new Query())
				->select('*')
				->from(['{{%volumefolders}}'])
				->where([
						'volumeId' => $settings['volumeId'],
						'parentId' => $folder['id'],
						'name' => $oldSubfolder])
				->one();

				if ($subFolder)
				{
					$volumeFolder = VolumeFolderRecord::findOne($subFolder['id']);
					$volumeFolder->name = $slider->handle;
					return $volumeFolder->save();
				}
			}
		}

		return false;
	}

	/**
	 * Create a secuencial string for the "name" and "handle" fields if they are already taken
	 *
	 * @param string
	 * @param string
	 * return string
	 */
	public function getFieldAsNew($field, $value)
	{
		$newField = null;
		$i        = 1;
		$band     = true;
		do
		{
			$newField = $field == "handle" ? $value . $i : $value . " " . $i;
			$slider   = $this->getFieldValue($field, $newField);
			if (is_null($slider))
			{
				$band = false;
			}

			$i++;
		}
		while ($band);

		return $newField;
	}

	/**
	 * Returns the value of a given field
	 *
	 * @param string $field
	 * @param string $value
	 *
	 * @return $slider
	 */
	public function getFieldValue($field, $value)
	{
		$result = SliderRecord::findOne([$field => $value]);

		return $result;
	}

	/**
	 * Create a secuencial string for "handle" if it's already taken
	 *
	 * @param string
	 * @param string
	 * return string
	 */
	public function getHandleAsNew($value, $isVolume = false)
	{
		$newHandle = $value;
		$aux       = true;
		$i         = 1;
		do
		{
			if ($i > 1)
			{
				$newHandle = $value . $i;
			}

			$field = $this->getFieldHandle($newHandle);

			if ($isVolume)
			{
				$field = $this->getVolumeHandle($newHandle);
			}

			if (is_null($field))
			{
				$aux = false;
			}

			$i++;
		}
		while ($aux);

		return $newHandle;
	}

	/**
	 * Returns the value of a given field
	 *
	 * @param string $field
	 * @param string $value
	 *
	 * @return FieldRecord
	 */
	public function getFieldHandle($value)
	{
		$result = FieldRecord::find()
			->where(['handle' => $value])
			->one();

		return $result;
	}

	/**
	 * Returns the value valume of a given field
	 *
	 * @param string $field
	 * @param string $value
	 *
	 * @return VolumeRecord
	 */
	public function getVolumeHandle($value)
	{
		$result = VolumeRecord::find()
			->where(['handle' => $value])
			->one();

		return $result;
	}

	public function getSettings()
	{
		$settings = (new Query())
			->select('settings')
			->from(['{{%plugins}}'])
			->where(['handle' => 'enupal-slider'])
			->one();

		$settings = json_decode($settings['settings'], true);

		return $settings;
	}

	public function getVolumeFolder($slider)
	{
		$settings = $this->getSettings();
		$sources  = [];

		if (isset($settings['volumeId']))
		{
			$folder = (new Query())
			->select('*')
			->from(['{{%volumefolders}}'])
			->where(['volumeId' => $settings['volumeId']])
			->one();

			$sources = ['folder:'.$folder['id']];

			$subFolder = (new Query())
			->select('*')
			->from(['{{%volumefolders}}'])
			->where([
					'volumeId' => $settings['volumeId'],
					'parentId' => $folder['id'],
					'name' => $slider->handle])
			->one();

			if ($subFolder)
			{
				$sources = ['folder:'.$folder['id'].'/folder:'.$subFolder['id']];
			}
		}

		return $sources;
	}

	public function getDataAttributes($slider)
	{
		$settings = $this->getDefaultOptions($slider);
		$data     = "";

		foreach ($settings as $setting => $value)
		{
			$data .= "data-enupalslider-{$setting}='{$value}' ";
		}

		return $data;
	}

	public function getEnupalSliderPath()
	{
		$defaultTemplate = Craft::getAlias('@enupal/slider/templates/_frontend/');

		return $defaultTemplate;
	}

	public function getSliderByHandle(string $handle, int $siteId = null)
	{
		$query = SliderElement::find();
		$query->handle($handle);
		$query->siteId($siteId);
		// @todo - research next function
		#$query->enabledForSite(false);

		return $query->one();
	}

	/**
	 * Get default options
	 *
	 * @return array Default slide options data
	 */
	public function getDefaultOptions($slider)
	{
		return [
			'mode' => $slider->mode,
			'speed' => $slider->speed,
			'slide-margin' => $slider->slideMargin,
			'start-slide' => 0,
			'random-start' => $slider->randomStart,
			'slide-selector' => $slider->slideSelector,
			'infinite-loop' => $slider->infiniteLoop,
			'hide-control-on-end' => 'false',
			'captions' => $slider->captions,
			'ticker' => $slider->ticker,
			'ticker-hover' => $slider->tickerHover,
			'adaptive-height' => $slider->adaptiveHeight,
			'adaptive-height-speed' => $slider->adaptiveHeightSpeed,
			'video' => $slider->video,
			'responsive' => $slider->responsive,
			'use-css' => $slider->useCss,
			'easing' => $slider->easing,
			'preload-images' => $slider->preloadImages,
			'touch-enabled' => $slider->touchEnabled,
			'swipe-threshold' => $slider->swipeThreshold,
			'one-to-one-touch' => 'true',
			'prevent-default-swipe-x' => $slider->preventDefaultSwipeX,
			'prevent-default-swipe-y' => $slider->preventDefaultSwipeY,

			'pager' => $slider->pager,
			'pager-type' => $slider->pagerType,
			'pager-short-separator' => $slider->pagerShortSeparator,
			'pager-selector' => $slider->pagerSelector,

			'controls' =>  $slider->controls ,
			'next-text' =>  $slider->nextText ,
			'prev-text' =>  $slider->prevText,
			'next-selector' =>  $slider->nextSelector,
			'prev-selector' =>  $slider->prevSelector,
			'auto-controls' =>  $slider->autoControls,
			'start-text' =>  $slider->startText,
			'stop-text' =>  $slider->stopText,
			'auto-controls-combine' =>  $slider->autoControlsCombine,
			'auto-controls-selector' =>  $slider->autoControlsSelector,
			'auto-keyboard-enabled' =>  $slider->keyboardEnabled,

			'auto' => $slider->auto,
			'pause' => $slider->pause,
			'auto-start' => $slider->autoStart,
			'auto-direction' => $slider->autoDirection,
			'auto-hover' => $slider->autoHover,
			'auto-delay' => $slider->autoDelay,

			'min-slides' => $slider->minSlides,
			'max-slides' => $slider->maxSlides,
			'move-slides' => $slider->moveSlides,
			'slide-width' => $slider->slideWidth,
			'slide-shrink-items' => $slider->shrinkItems,
		];
	}

	/**
	 * Get default options
	 *
	 * @return array Default slide options data
	 */
	public function getDefaultOptionsByAjax($slider)
	{
		return [
			'mode' => $slider->mode,
			'speed' => $slider->speed,
			'slideMargin' => $slider->slideMargin,
			'startSlide' => 0,
			'randomStart' => $slider->randomStart,
			'slideSelector' => $slider->slideSelector,
			'infiniteLoop' => $slider->infiniteLoop,
			'hideControlOnEnd' => 'false',
			'captions' => $slider->captions,
			'ticker' => $slider->ticker,
			'tickerHover' => $slider->tickerHover,
			'adaptiveHeight' => $slider->adaptiveHeight,
			'adaptiveHeightSpeed' => $slider->adaptiveHeightSpeed,
			'video' => $slider->video,
			'responsive' => $slider->responsive,
			'useCSS' => $slider->useCss ? 1 : 0,
			'easing' => $slider->easing,
			'preloadImages' => $slider->preloadImages,
			'touchEnabled' => $slider->touchEnabled,
			'swipeThreshold' => $slider->swipeThreshold,
			'oneToOneTouch' => 'true',
			'preventDefaultSwipeX' => $slider->preventDefaultSwipeX,
			'preventDefaultSwipeY' => $slider->preventDefaultSwipeY,

			'pager' => $slider->pager,
			'pagerType' => $slider->pagerType,
			'pagerShortSeparator' => $slider->pagerShortSeparator,
			'pagerSelector' => $slider->pagerSelector,

			'controls' =>  $slider->controls ,
			'nextText' =>  $slider->nextText ,
			'prevText' =>  $slider->prevText,
			'nextSelector' =>  $slider->nextSelector,
			'prevSelector' =>  $slider->prevSelector,
			'autoControls' =>  $slider->autoControls,
			'startText' =>  $slider->startText,
			'stopText' =>  $slider->stopText,
			'autoControlsCombine' =>  $slider->autoControlsCombine,
			'autoControlsSelector' =>  $slider->autoControlsSelector,
			'autoKeyboardEnabled' =>  $slider->keyboardEnabled,

			'auto' => $slider->auto,
			'pause' => $slider->pause,
			'autoStart' => $slider->autoStart,
			'autoDirection' => $slider->autoDirection,
			'autoHover' => $slider->autoHover,
			'autoDelay' => $slider->autoDelay,

			'minSlides' => $slider->minSlides,
			'maxSlides' => $slider->maxSlides,
			'moveSlides' => $slider->moveSlides,
			'slideWidth' => $slider->slideWidth,
			'slideS0hrinkItems' => $slider->shrinkItems,
		];
	}

	public function getSliderPath()
	{
		// Get the public path of Craft CMS
		$debugTrace = debug_backtrace();
		$initialCalledFile = count($debugTrace) ? $debugTrace[count($debugTrace) - 1]['file'] : __FILE__;
		$publicFolderPath = dirname($initialCalledFile);
		$publicFolderPath = $publicFolderPath."/enupalslider";
		$publicFolderPath = FileHelper::normalizePath($publicFolderPath);

		return $publicFolderPath;
	}

	public function populateSliderFromPost(SliderElement $slider)
	{
		$request = Craft::$app->getRequest();

		$slider->slides         = $request->getBodyParam('slides');
		$slider->mode           = $request->getBodyParam('mode');
		$slider->speed          = $request->getBodyParam('speed');
		$slider->slideMargin    = $request->getBodyParam('slideMargin');
		$slider->randomStart    = $request->getBodyParam('randomStart');
		$slider->slideSelector  = $request->getBodyParam('slideSelector');
		$slider->infiniteLoop   = $request->getBodyParam('infiniteLoop');
		$slider->captions       = $request->getBodyParam('captions');
		$slider->ticker         = $request->getBodyParam('ticker');
		$slider->tickerHover    = $request->getBodyParam('tickerHover');
		$slider->adaptiveHeight = $request->getBodyParam('adaptiveHeight');
		$slider->video          = $request->getBodyParam('video');
		$slider->responsive     = $request->getBodyParam('responsive');
		$slider->useCss         = $request->getBodyParam('useCss');
		$slider->easing         = $request->getBodyParam('easing');
		$slider->preloadImages  = $request->getBodyParam('preloadImages');
		$slider->touchEnabled   = $request->getBodyParam('touchEnabled');
		$slider->swipeThreshold = $request->getBodyParam('swipeThreshold');
		$slider->adaptiveHeightSpeed  = $request->getBodyParam('adaptiveHeightSpeed');
		$slider->preventDefaultSwipeX = $request->getBodyParam('preventDefaultSwipeX');
		$slider->preventDefaultSwipeX = $request->getBodyParam('preventDefaultSwipeX');
		//Pager
		$slider->pager                = $request->getBodyParam('pager');
		$slider->pagerType            = $request->getBodyParam('pagerType');
		$slider->pagerShortSeparator  = $request->getBodyParam('pagerShortSeparator');
		$slider->pagerSelector        = $request->getBodyParam('pagerSelector');
		$slider->thumbnailPager       = $request->getBodyParam('thumbnailPager');
		//Controls
		$slider->controls             = $request->getBodyParam('controls');
		$slider->nextText             = $request->getBodyParam('nextText');
		$slider->prevText             = $request->getBodyParam('prevText');
		$slider->nextSelector         = $request->getBodyParam('nextSelector');
		$slider->prevSelector         = $request->getBodyParam('prevSelector');
		$slider->autoControls         = $request->getBodyParam('autoControls');
		$slider->startText            = $request->getBodyParam('startText');
		$slider->stopText             = $request->getBodyParam('stopText');
		$slider->autoControlsCombine  = $request->getBodyParam('autoControlsCombine');
		$slider->autoControlsSelector = $request->getBodyParam('autoControlsSelector');
		$slider->keyboardEnabled      = $request->getBodyParam('keyboardEnabled');
		//Auto
		$slider->auto                 = $request->getBodyParam('auto');
		$slider->stopAutoOnClick      = $request->getBodyParam('stopAutoOnClick');
		$slider->pause                = $request->getBodyParam('pause');
		$slider->autoStart            = $request->getBodyParam('autoStart');
		$slider->autoDirection        = $request->getBodyParam('autoDirection');
		$slider->autoHover            = $request->getBodyParam('autoHover');
		$slider->autoDelay            = $request->getBodyParam('autoDelay');
		//Carousel
		$slider->minSlides            = $request->getBodyParam('minSlides');
		$slider->maxSlides            = $request->getBodyParam('maxSlides');
		$slider->moveSlides           = $request->getBodyParam('moveSlides');
		$slider->slideWidth           = $request->getBodyParam('slideWidth');
		$slider->shrinkItems          = $request->getBodyParam('shrinkItems');

		return $slider;
	}

	public function removeVolumeAndFields()
	{
		$plugin = Craft::$app->getPlugins()->getPlugin('enupal-slider');
		$fields = Craft::$app->getFields();

		$fieldsToDelete = $fields->getFieldsByElementType(SliderElement::class);

		foreach ($fieldsToDelete as $key => $field)
		{
			$fields->deleteFieldById($field->id);
		}

		// Let's delete the volume
		if (isset($plugin->settings['volumeId']))
		{
			Craft::$app->getVolumes()->deleteVolumeById($plugin->settings['volumeId']);
		}
	}
}
