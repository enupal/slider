<?php
/**
 * @link      https://enupal.com/
 * @copyright Copyright (c) Enupal
 * @license   http://enupal.com/craft-plugins/license
 */

namespace enupal\slider\elements;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use yii\base\ErrorHandler;
use craft\db\Query;
use craft\helpers\UrlHelper;
use yii\base\InvalidConfigException;
use craft\elements\actions\Delete;
use craft\validators\HandleValidator;
use craft\validators\UniqueValidator;

use enupal\slider\elements\db\SliderQuery;
use enupal\slider\records\Slider as SliderRecord;
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

	// Slides of the Slider
	public $slides;

	public $groupId;

	// Type of transition between slides.
	public $mode = 'horizontal';

	// Slide transition duration (in ms).
	public $speed = 500;

	//  Start slider on a random slide.
	public $randomStart = false;

	//If checked, clicking "Next" while on the last slide will transition to the first slide and vice-versa.
	public $infiniteLoop = true;

	// Include image captions.
	public $captions = true;

	// Use slider in ticker mode (similar to a news ticker).
	public $ticker = false;

	// Ticker will pause when mouse hovers over slider. Note: this functionality does NOT work if using CSS transitions!
	public $tickerHover = false;

	// Dynamically adjust slider height based on each slide's height.
	public $adaptiveHeight = false;

	//  Slide height transition duration (in ms). Note: only used if Adaptive Height is checked.
	public $adaptiveHeightSpeed = 500;

	// Check this if any slides contain a video.
	public $video = false;

	//  Enable or disable auto resize of the slider. Useful if you need to use fixed width sliders.
	public $responsive = true;

	//  If checked, CSS transitions will be used for horizontal and vertical slide animations (this uses native hardware acceleration). If unchecked, jQuery animate() will be used.
	public $useCss = true;

	// The type of "easing" to use during transitions.
	public $easing = null;

	//  If "all", preloads all images before starting the slider. If "visible", preloads only images in the initially visible slides before starting the slider (tip: use "visible" if all slides are identical dimensions).
	public $preloadImages = 'visible';

	//  If checked, slider will allow touch swipe transitions.
	public $touchEnabled = true;

	// Amount of pixels a touch swipe needs to exceed in order to execute a slide transition. Note: Only used if Touch Enabled is checked.
	public $swipeThreshold = 50;

	// If checked, touch screen will not move along the x-axis as the finger swipes.
	public $preventDefaultSwipeX = true;

	// If checked, touch screen will not move along the y-axis as the finger swipes.
	public $preventDefaultSwipeY = false;

	// Margin between each slide.
	public $slideMargin = 0;

	// Element to use as slides (ex. 'div.slide'). Note: by default, bxSlider will use all immediate children of the slider element.
	public $slideSelector = '';

	// Pager - Properties
	// =========================================================================
	public $pager               = true;
	public $pagerType           = 'full';
	public $pagerShortSeparator = ' / ';
	public $pagerSelector       = '';
	public $thumbnailPager      = false;

	// Controls - Properties
	// =========================================================================

	public $controls             = true;
	public $nextText             = 'Next';
	public $prevText             = 'Prev';
	public $nextSelector         = null;
	public $prevSelector         = null;
	public $autoControls         = false;
	public $startText            = 'Start';
	public $stopText             = 'Stop';
	public $autoControlsCombine  = false;
	public $autoControlsSelector = null;
	public $keyboardEnabled      = false;

	// Auto - Properties
	// =========================================================================

	public $auto            = false;
	public $stopAutoOnClick = false;
	public $pause           = 4000;
	public $autoStart       = false;
	public $autoDirection   = 'next';
	public $autoHover       = false;
	public $autoDelay       = 0;

	// Carousel - Properties
	// =========================================================================

	public $minSlides  = 1;
	public $maxSlides  = 1;
	public $moveSlides = 0;
	public $slideWidth = 0;
	public $shrinkItems = false;

	/**
	 * Returns the field context this element's content uses.
	 *
	 * @access protected
	 * @return string
	 */
	public function getFieldContext(): string
	{
		return 'global';
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
			'enupal-slider/slider/edit/'.$this->id
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
			'confirmationMessage' => Slider::t('Are you sure you want to delete the selected sliders?'),
			'successMessage' => Slider::t('Sliders deleted.'),
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
		$attributes['dateUpdated'] = ['label' => Slider::t('Number of Slides')];

		return $attributes;
	}

	protected static function defineDefaultTableAttributes(string $source): array
	{
		$attributes = ['name', 'handle', 'dateUpdated'];

		return $attributes;
	}

	/**
	 * @inheritdoc
	 */
	protected function tableAttributeHtml(string $attribute): string
	{
		switch ($attribute)
		{
			case 'handle':
			{
				return '<code>' . $this->handle . '</code>';
			}
			case 'dateUpdated':
			{
				$slides = json_decode($this->slides, true);

				return count($slides);
			}
		}

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

		$record->name                 = $this->name;
		$record->handle               = $this->handle;
		$record->slides               = $this->slides;
		$record->mode                 = $this->mode;
		$record->speed                = $this->speed;
		$record->slideMargin          = $this->slideMargin;
		$record->randomStart          = $this->randomStart;
		$record->slideSelector        = $this->slideSelector;
		$record->infiniteLoop         = $this->infiniteLoop;
		$record->captions             = $this->captions;
		$record->ticker               = $this->ticker;
		$record->tickerHover          = $this->tickerHover;
		$record->adaptiveHeight       = $this->adaptiveHeight;
		$record->adaptiveHeightSpeed  = $this->adaptiveHeightSpeed;
		$record->video                = $this->video;
		$record->responsive           = $this->responsive;
		$record->useCss               = $this->useCss;
		$record->easing               = $this->easing;
		$record->preloadImages        = $this->preloadImages;
		$record->touchEnabled         = $this->touchEnabled;
		$record->swipeThreshold       = $this->swipeThreshold;
		$record->preventDefaultSwipeX = $this->preventDefaultSwipeX;
		$record->preventDefaultSwipeY = $this->preventDefaultSwipeY;
		//Pager
		$record->pager                = $this->pager;
		$record->pagerType            = $this->pagerType;
		$record->pagerShortSeparator  = $this->pagerShortSeparator;
		$record->pagerSelector        = $this->pagerSelector;
		$record->thumbnailPager       = $this->thumbnailPager;
		//Controls
		$record->controls             = $this->controls;
		$record->nextText             = $this->nextText;
		$record->prevText             = $this->prevText;
		$record->nextSelector         = $this->nextSelector;
		$record->prevSelector         = $this->prevSelector;
		$record->autoControls         = $this->autoControls;
		$record->startText            = $this->startText;
		$record->stopText             = $this->stopText;
		$record->autoControlsCombine  = $this->autoControlsCombine;
		$record->autoControlsSelector = $this->autoControlsSelector;
		$record->keyboardEnabled      = $this->keyboardEnabled;
		//Auto
		$record->auto                 = $this->auto;
		$record->stopAutoOnClick      = $this->stopAutoOnClick;
		$record->pause                = $this->pause;
		$record->autoStart            = $this->autoStart;
		$record->autoDirection        = $this->autoDirection;
		$record->autoHover            = $this->autoHover;
		$record->autoDelay            = $this->autoDelay;
		//Carousel
		$record->minSlides            = $this->minSlides;
		$record->maxSlides            = $this->maxSlides;
		$record->moveSlides           = $this->moveSlides;
		$record->slideWidth           = $this->slideWidth;
		$record->shrinkItems          = $this->shrinkItems;

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
			[['name', 'handle'], UniqueValidator::class, 'targetClass' => SliderRecord::class],
		];
	}
}