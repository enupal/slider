<?php
namespace enupal\slider\services;

use Craft;
use yii\base\Component;
use craft\fields\RichText;
use craft\fields\Checkboxes;
use craft\elements\Asset;
use craft\volumes\Local;
use craft\base\Field;
use craft\records\Field as FieldRecord;
use craft\records\Volume as VolumeRecord;
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
		$slider        = new SliderElement;

		$handle        = $this->getHandleAsNew("enupalSliderHtml");
		$htmlField = $fieldsService->createField([
			'type' => RichText::class,
			'name' => Slider::t('Html'),
			'handle' => $handle,
			'instructions' => Slider::t('Override your image with custom HTML'),
			'translationMethod' => Field::TRANSLATION_METHOD_NONE,
		]);
		// Save our field
		Craft::$app->content->fieldContext = $slider->getFieldContext();
		Craft::$app->fields->saveField($htmlField);

		$handle        = $this->getHandleAsNew("enupalSliderSource");
		$sourceField = $fieldsService->createField([
			'type' => Checkboxes::class,
			'name' => Slider::t('Source'),
			'handle' => $handle,
			'instructions' => Slider::t('What should display this slide?'),
			'settings'  => '{"options":[{"label":"Image","value":"image","default":"1"},{"label":"Both (Image and Html)","value":"bothImageAndHtml","default":""},{"label":"Just hmtl","value":"justHmtl","default":""}]}',
			'translationMethod' => Field::TRANSLATION_METHOD_NONE,
		]);
		// Save our field
		Craft::$app->content->fieldContext = $slider->getFieldContext();
		Craft::$app->fields->saveField($sourceField);

		// Create a tab
		$tabName           = Slider::t('Default');
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
		$volumeHandle = $this->getHandleAsNew('enupalSlider', true);

		/** @var Volume $volume */
		$volumes = Craft::$app->getVolumes();
		$volume = $volumes->createVolume([
			'id' => null,
			// let's add support for local just for now.
			'type' => Local::class,
			'name' => "Enupal Slider",
			'handle' => $volumeHandle,
			'hasUrls' => true,
			'url' => '/enupalslider/',
			'settings' => '{"path":"enupalslider"}'
		]);

		// Set the field layout
		$fieldLayout->type = Asset::class;
		$volume->setFieldLayout($fieldLayout);

		// save it
		$response = $volumes->saveVolume($volume);

		if (isset($volume->id) && $volume->id)
		{
			$settings = [
				'pluginNameOverride'=>'',
				'volumeId' => $volume->id
			];

			$settings = json_encode($settings);
			$affectedRows = Craft::$app->getDb()->createCommand()->update('plugins', [
				'settings' => $settings
				],
				[
				'handle' => 'enupalslider'
				]
			);
		}

		return $response;
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


}
