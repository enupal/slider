<?php
namespace enupal\slider\elements\db;

use Craft;
use craft\db\Query;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use yii\db\Connection;

use enupal\slider\Slider;

class SliderQuery extends ElementQuery
{

	// General - Properties
	// =========================================================================
	public $id;

	// Name of the Slider
	public $name;

	// Handle of the Slider
	public $handle;

	// Slides of the Slider
	public $slides;

	// GroupId of the Slider
	public $groupId;

	// Type of transition between slides.
	public $mode;

	// Slide transition duration (in ms).
	public $speed;

	//  Start slider on a random slide.
	public $randomStart;

	//If checked, clicking "Next" while on the last slide will transition to the first slide and vice-versa.
	public $infiniteLoop;

	// Include image captions.
	public $captions;

	// Use slider in ticker mode (similar to a news ticker).
	public $ticker;

	// Ticker will pause when mouse hovers over slider. Note: this functionality does NOT work if using CSS transitions!
	public $tickerHover;

	// Dynamically adjust slider height based on each slide's height.
	public $adaptiveHeight;

	//  Slide height transition duration (in ms). Note: only used if Adaptive Height is checked.
	public $adaptiveHeightSpeed;

	// Check this if any slides contain a video.
	public $video;

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

	/**
	 * @inheritdoc
	 */
	public function __set($name, $value)
	{
		parent::__set($name, $value);
	}

	/**
	 * @inheritdoc
	 */
	public function __construct($elementType, array $config = [])
	{
		// Default orderBy
		if (!isset($config['orderBy'])) {
			$config['orderBy'] = 'enupalslider_sliders.dateCreated';
		}

		parent::__construct($elementType, $config);
	}

	/**
	 * Sets the [[statusId]] property.
	 *
	 * @param int
	 *
	 * @return static self reference
	 */
	public function name($value)
	{
		$this->name = $value;

		return $this;
	}

	/**
	 * Sets the [[statusId]] property.
	 *
	 * @param int
	 *
	 * @return static self reference
	 */
	public function handle($value)
	{
		$this->handle = $value;

		return $this;
	}

	// Protected Methods
	// =========================================================================

	/**
	 * @inheritdoc
	 */
	protected function beforePrepare(): bool
	{
		$this->joinElementTable('enupalslider_sliders');

		$this->query->select([
			'enupalslider_sliders.name',
			'enupalslider_sliders.handle',
			'enupalslider_sliders.slides',
			'enupalslider_sliders.groupId',
			'enupalslider_sliders.mode',
			'enupalslider_sliders.speed',
			'enupalslider_sliders.slideMargin',
			'enupalslider_sliders.randomStart',
			'enupalslider_sliders.infiniteLoop',
			'enupalslider_sliders.captions',
			'enupalslider_sliders.ticker',
			'enupalslider_sliders.tickerHover',
			'enupalslider_sliders.adaptiveHeight',
			'enupalslider_sliders.adaptiveHeightSpeed',
			'enupalslider_sliders.video',
			'enupalslider_sliders.responsive',
			'enupalslider_sliders.useCss',
			'enupalslider_sliders.easing',
			'enupalslider_sliders.preloadImages',
			'enupalslider_sliders.touchEnabled',
			'enupalslider_sliders.swipeThreshold',
			'enupalslider_sliders.preventDefaultSwipeX',
			'enupalslider_sliders.preventDefaultSwipeY',
			//Pager
			'enupalslider_sliders.pager',
			'enupalslider_sliders.pagerType',
			'enupalslider_sliders.pagerShortSeparator',
			'enupalslider_sliders.pagerSelector',
			//Controls
			'enupalslider_sliders.controls',
			'enupalslider_sliders.nextText',
			'enupalslider_sliders.prevText',
			'enupalslider_sliders.nextSelector',
			'enupalslider_sliders.prevSelector',
			'enupalslider_sliders.autoControls',
			'enupalslider_sliders.startText',
			'enupalslider_sliders.stopText',
			'enupalslider_sliders.autoControlsCombine',
			'enupalslider_sliders.autoControlsSelector',
			'enupalslider_sliders.keyboardEnabled',
			//Auto
			'enupalslider_sliders.auto',
			'enupalslider_sliders.stopAutoOnClick',
			'enupalslider_sliders.pause',
			'enupalslider_sliders.autoStart',
			'enupalslider_sliders.autoDirection',
			'enupalslider_sliders.autoHover',
			'enupalslider_sliders.autoDelay',
			//Carousel
			'enupalslider_sliders.minSlides',
			'enupalslider_sliders.maxSlides',
			'enupalslider_sliders.moveSlides',
			'enupalslider_sliders.slideWidth',
			'enupalslider_sliders.shrinkItems',
			'enupalslider_sliders.uid',
		]);

		if ($this->id) {
			$this->subQuery->andWhere(Db::parseParam(
				'enupalslider_sliders.id', $this->id)
			);
		}

		if ($this->name) {
			$this->subQuery->andWhere(Db::parseParam(
				'enupalslider_sliders.name', $this->name)
			);
		}

		if ($this->handle) {
			$this->subQuery->andWhere(Db::parseParam(
				'enupalslider_sliders.handle', $this->handle)
			);
		}

		return parent::beforePrepare();
	}
}
