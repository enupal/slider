<?php
namespace enupal\slider\services;

use Craft;
use yii\base\Component;
use craft\fields\RichText;
use craft\fields\RadioButtons;
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

		if (!$slider->hasErrors())
		{
			$transaction = Craft::$app->db->getTransaction() === null ? Craft::$app->db->beginTransaction() : null;
			try
			{
				if (Craft::$app->elements->saveElement($slider, false))
				{
					if ($transaction !== null)
					{
						$transaction->commit();
					}

					return true;
				}
			}
			catch (\Exception $e)
			{
				if ($transaction !== null)
				{
					$transaction->rollback();
				}

				throw $e;
			}
		}
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

			$htmlHandle    = $this->getHandleAsNew("enupalSliderHtml");
			$htmlField = $fieldsService->createField([
				'type' => RichText::class,
				'name' => Slider::t('Html'),
				'groupId' => $fieldGroupId,
				'handle' => $htmlHandle,
				'instructions' => Slider::t('Override your image with custom HTML'),
				'translationMethod' => Field::TRANSLATION_METHOD_NONE,
			]);
			// Save our field
			#Craft::$app->content->fieldContext = $slider->getFieldContext();
			Craft::$app->fields->saveField($htmlField);

			$sourceHandle = $this->getHandleAsNew("enupalSliderSource");
			$sourceField  = $fieldsService->createField([
				'type' => RadioButtons::class,
				'name' => Slider::t('Source'),
				'handle' => $sourceHandle,
				'groupId' => $fieldGroupId,
				'instructions' => Slider::t('What should display this slide?'),
				'settings'  => '{"options":[{"label":"Image","value":"image","default":"1"},{"label":"Both (Image and Html)","value":"bothImageAndHtml","default":""},{"label":"Just hmtl","value":"justHmtl","default":""}]}',
				'translationMethod' => Field::TRANSLATION_METHOD_NONE,
			]);
			// Save our field
			#Craft::$app->content->fieldContext = $slider->getFieldContext();
			Craft::$app->fields->saveField($sourceField);

			// Create a tab
			$tabName           = Slider::t('Enupal Slider');
			$requiredFields    = array();
			$postedFieldLayout = array();

			// Add our new fields
			if (isset($htmlField) && $htmlField->id != null)
			{
				$postedFieldLayout[$tabName][] = $htmlField->id;
			}

			if (isset($sourceField) && $sourceField->id != null)
			{
				$postedFieldLayout[$tabName][] = $sourceField->id;
			}

			// Set the field layout
			$fieldLayout = Craft::$app->fields->assembleLayout($postedFieldLayout, $requiredFields);

			$fieldLayout->type = FormElement::class;
			$volumeHandle = $this->getHandleAsNew('EnupalSlider', true);

			/** @var Volume $volume */
			$volumes = Craft::$app->getVolumes();
			$volume = $volumes->createVolume([
				'id' => null,
				// let's add support for local just for now.
				'type' => Local::class,
				'name' => $volumeHandle,
				'handle' => $volumeHandle,
				'hasUrls' => true,
				'url' => '/enupalslider/',
				'settings' => '{"path":"enupalslider"}'
			]);

			// Set the field layout
			$fieldLayout->type = Asset::class;
			$volume->setFieldLayout($fieldLayout);
			$volume->validate();
			$errors = $volume->getErrors();

			// save it
			$response = $volumes->saveVolume($volume);

			if ($response)
			{
				$settings = [
					'pluginNameOverride' => '',
					'volumeId'           => $volume->id,
					'sourceHandle'       => $sourceHandle,
					'htmlHandle'         => $htmlHandle
				];

				$settings = json_encode($settings);
				$affectedRows = Craft::$app->getDb()->createCommand()->update('plugins', [
					'settings' => $settings
					],
					[
					'handle' => 'enupalslider'
					]
				)->execute();
			}

			$transaction->commit();

			return $response;

		}
		catch (Exception $e)
		{
			$transaction->rollBack();
			Slider::log('Failed to save element: '.$e->getMessage(), 'error');
			throw $e;
		}
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
			$settings = (new Query())
				->select('settings')
				->from(['{{%plugins}}'])
				->where(['handle' => 'enupalslider'])
				->one();

			$sources = null;
			$settings = json_decode($settings['settings'], true);

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
			$form     = $this->getFieldValue($field, $newField);
			if (is_null($form))
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
	 * @return $form
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
		$newHandle = null;
		$aux       = true;
		$i         = 1;
		do
		{
			$newHandle = $value . $i;
			$field     = $this->getFieldHandle($newHandle);

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
			->where(['handle' => 'enupalslider'])
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
		$settings = get_class_vars(get_class($slider));
		$data     = "";

		foreach ($settings as $setting => $value)
		{
			$data .= "data-{$setting}='{$value}' ";
		}

		return $data;
	}

	public function getEnupalSliderPath()
	{
		$defaultTemplate = Craft::$app->path->getPluginsPath() . '/enupalslider/src/templates/_frontend/';

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
			'mode' => $slider->mode ? $slider->mode : 'horizontal',
			'speed' => 500,
			'slide_margin' => 0,
			'start_slide' => 0,
			'random_start' => 'false',
			'slide_selector' => '',
			'infinite_loop' => 'true',
			'hide_control_on_end' => 'false',
			'captions' => 'true',
			'ticker' => 'false',
			'ticker_hover' => 'false',
			'adaptive_height' => 'false',
			'adaptive_height_speed' => 500,
			'video' => 'false',
			'responsive' => 'true',
			'use_css' => 'true',
			'easing' => 'null',
			'preload_images' => 'visible',
			'touch_enabled' => 'true',
			'swipe_threshold' => 50,
			'one_to_one_touch' => 'true',
			'prevent_default_swipe_x' => 'true',
			'prevent_default_swipe_y' => 'false',

			'pager' => 'true',
			'pager_type' => 'full',
			'pager_short_separator' => ' / ',
			'pager_selector' => '',

			'controls' => 'true',
			'next_text' => 'Next',
			'prev_text' => 'Prev',
			'next_selector' => 'null',
			'prev_selector' => 'null',
			'auto_controls' => 'false',
			'start_text' => 'Start',
			'stop_text' => 'Stop',
			'auto_controls_combine' => 'false',
			'auto_controls_selector' => 'null',

			'auto' => 'false',
			'pause' => 4000,
			'auto_start' => 'true',
			'auto_direction' => 'next',
			'auto_hover' => 'false',
			'auto_delay' => 0,

			'min_slides' => 1,
			'max_slides' => 1,
			'move_slides' => 0,
			'slide_width' => 0,
		];
	}

}
