<?php
namespace enupal\slider\elements;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use yii\base\ErrorHandler;
use craft\db\Query;
use craft\helpers\UrlHelper;
use yii\base\InvalidConfigException;
use craft\elements\actions\Delete;

use enupal\slider\elements\db\SliderQuery;
use enupal\slider\records\Form as SliderRecord;
use enupal\slider\Slider;

/**
 * Slider represents a entry element.
 */
class Slider extends Element
{
	// Properties
	// =========================================================================

	// General - Properties
	// =========================================================================
	public $id;

	// Name of the Slider
	public $name;

	// Handle of the Slider
	public $handle;

	// Images of the Slider
	public $images;

	// Type of transition between slides.
	public $mode;

	// Slide transition duration (in ms).
	public $speed;

	//  Start slider on a random slide.
	public $randomStart;

	//If checked, clicking "Next" while on the last slide will transition to the first slide and vice-versa.
	public $infiniteLoop;

	// Include image captions.
	public $hasCaptions;

	// Use slider in ticker mode (similar to a news ticker).
	public $isTicker;

	// Ticker will pause when mouse hovers over slider. Note: this functionality does NOT work if using CSS transitions!
	public $tickerHover;

	// Dynamically adjust slider height based on each slide's height.
	public $adaptiveHeight;

	//  Slide height transition duration (in ms). Note: only used if Adaptive Height is checked.
	public $adaptiveHeightSpeed;

	// Check this if any slides contain a video.
	public $hasVideo;

	//  Enable or disable auto resize of the slider. Useful if you need to use fixed width sliders.
	public $responsive;

	//  If checked, CSS transitions will be used for horizontal and vertical slide animations (this uses native hardware acceleration). If unchecked, jQuery animate() will be used.
	public $useCss;

	// The type of "easing" to use during transitions.
	public $easing;

	//  If "all", preloads all images before starting the slider. If "visible", preloads only images in the initially visible slides before starting the slider (tip: use "visible" if all slides are identical dimensions).
	public $preloadImages;

	//  If checked, slider will allow touch swipe transitions.
	public $touchEnabled;

	// Amount of pixels a touch swipe needs to exceed in order to execute a slide transition. Note: Only used if Touch Enabled is checked.
	public $swipeThreshold;

	// If checked, touch screen will not move along the x-axis as the finger swipes.
	public $preventDefaultSwipeX;

	// If checked, touch screen will not move along the y-axis as the finger swipes.
	public $preventDefaultSwipeY;

	// Margin between each slide.
	public $slideMargin;

	// Element to use as slides (ex. 'div.slide'). Note: by default, bxSlider will use all immediate children of the slider element.
	public $slideSelector;

	// Pager - Properties
	// =========================================================================


	// Controls - Properties
	// =========================================================================

	// Auto - Properties
	// =========================================================================

	// Carousel - Properties
	// =========================================================================

	/**
	 * Returns the field context this element's content uses.
	 *
	 * @access protected
	 * @return string
	 */
	public function getFieldContext(): string
	{
		return 'enupalSlider:' . $this->id;
	}

	/**
	 * Returns the element type name.
	 *
	 * @return string
	 */
	public static function displayName(): string
	{
		return Slider::t('Sliders');
	}

	/**
	 * @inheritdoc
	 */
	public static function refHandle()
	{
		return 'sliders';
	}

	/**
	 * @inheritdoc
	 */
	public static function hasContent(): bool
	{
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public static function hasTitles(): bool
	{
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public static function isLocalized(): bool
	{
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public static function hasStatuses(): bool
	{
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function getCpEditUrl()
	{
		return UrlHelper::cpUrl(
			'enupal-slider/sliders/edit/'.$this->id
		);
	}

	/**
	 * Use the name as the string representation.
	 *
	 * @return string
	 */
	/** @noinspection PhpInconsistentReturnPointsInspection */
	public function __toString()
	{
		try
		{
			// @todo - For some reason the Title returns null possible Craft3 bug
			return $this->name;
		} catch (\Exception $e) {
			ErrorHandler::convertExceptionToError($e);
		}
	}

	/**
	 * @inheritdoc
	 *
	 * @return FormQuery The newly created [[FormQuery]] instance.
	 */
	public static function find(): ElementQueryInterface
	{
		return new SliderQuery(get_called_class());
	}

	/**
	 * @inheritdoc
	 */
	protected static function defineSources(string $context = null): array
	{
		$sources = [
			[
			'key'   => '*',
			'label' => Slider::t('All Sliders'),
			]
		];

		// @todo - $groups = Slider::$app->groups->getAllSliderGroups();
		$groups = [];

		foreach ($groups as $group)
		{
			$key = 'group:' . $group->id;

			$sources[] = [
				'key'      => $key,
				'label'    => Slider::t($group->name),
				'data'     => ['id' => $group->id],
				'criteria' => ['groupId' => $group->id]
			];
		}

		return $sources;
	}

	/**
	 * @inheritdoc
	 */
	protected static function defineActions(string $source = null): array
	{
		$actions = [];

		// Delete
		$actions[] = Craft::$app->getElements()->createAction([
			'type' => Delete::class,
			'confirmationMessage' => Slider::t('Are you sure you want to delete the selected entries?'),
			'successMessage' => Slider::t('Entries deleted.'),
		]);

		return $actions;
	}

	/**
	 * @inheritdoc
	 */
	protected static function defineSearchableAttributes(): array
	{
		return ['name', 'handle'];
	}

	/**
	 * @inheritdoc
	 */
	protected static function defineSortOptions(): array
	{
		$attributes = [
			'enupalslider_sliders.name' => Slider::t('Slider Name'),
			'elements.dateCreated'      => Slider::t('Date Created'),
			'elements.dateUpdated'      => Slider::t('Date Updated'),
		];

		return $attributes;
	}

	/**
	 * @inheritdoc
	 */
	protected static function defineTableAttributes(): array
	{
		$attributes['name']        = ['label' => Slider::t('Slider Name')];
		$attributes['handle']      = ['label' => Slider::t('Slider Handle')];
		// @todo - $attributes['numberOfSlides'] = ['label' => Slider::t('Date Created')];
		$attributes['dateUpdated'] = ['label' => Slider::t('Date Updated')];

		return $attributes;
	}

	protected static function defineDefaultTableAttributes(string $source): array
	{
		$attributes = ['name', 'handle', 'dateCreated', 'dateUpdated'];

		return $attributes;
	}

	/**
	 * @inheritdoc
	 */
	protected function tableAttributeHtml(string $attribute): string
	{
		return parent::tableAttributeHtml($attribute);
	}

	/**
	 * @inheritdoc
	 * @throws Exception if reasons
	 */
	public function afterSave(bool $isNew)
	{
		// Get the Slider record
		if (!$isNew)
		{
			$record = SliderRecord::findOne($this->id);

			if (!$record)
			{
				throw new Exception('Invalid Slider ID: '.$this->id);
			}
		} else
		{
			$record = new SliderRecord();
			$record->id = $this->id;
		}

		$record->name   = $this->name;
		$record->handle = $this->handle;
		$record->iamges = $this->iamges;

		$record->save(false);

		parent::afterSave($isNew);
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['name', 'handle'], 'required'],
			[['name', 'handle'], 'string', 'max' => 255],
			[
				['handle'],
				HandleValidator::class,
				'reservedWords' => ['id', 'dateCreated', 'dateUpdated', 'uid', 'title']
			],
			[['name', 'handle'], UniqueValidator::class, 'targetClass' => FormRecord::class],
		];
	}
}