<?php
/**
 * EnupalSlider plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2017 Enupal
 */

namespace enupal\slider\migrations;

use Craft;
use craft\db\Connection;
use craft\db\Migration;
use craft\elements\User;
use craft\helpers\StringHelper;

/**
 * Installation Migration
 */
class Install extends Migration
{
	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{
		$this->createTables();
		$this->addForeignKeys();
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		$this->dropTableIfExists('{{%enupalslider_sliders}}');
		$this->dropTableIfExists('{{%enupalslider_groups}}');
	}

	/**
	 * Creates the tables.
	 *
	 * @return void
	 */
	protected function createTables()
	{
		$this->createTable('{{%enupalslider_sliders}}', [
			'id'                   => $this->primaryKey(),
			'name'                 => $this->string()->notNull(),
			'handle'               => $this->string()->notNull(),
			'slides'               => $this->string()->notNull(),
			'groupId'              => $this->integer(),
			'mode'                 => $this->string(),
			'speed'                => $this->integer(),
			'randomStart'          => $this->boolean()->defaultValue(false),
			'startSlide'           => $this->string(),
			'infiniteLoop'         => $this->boolean()->defaultValue(true),
			'captions'             => $this->boolean()->defaultValue(true),
			'ticker'               => $this->boolean()->defaultValue(false),
			'tickerHover'          => $this->boolean()->defaultValue(false),
			'adaptiveHeight'       => $this->boolean()->defaultValue(false),
			'adaptiveHeightSpeed'  => $this->integer(),
			'video'                => $this->boolean()->defaultValue(false),
			'responsive'           => $this->boolean()->defaultValue(false),
			'useCss'               => $this->boolean()->defaultValue(false),
			'easing'               => $this->string(),
			'preloadImages'        => $this->string(),
			'touchEnabled'         => $this->boolean()->defaultValue(false),
			'swipeThreshold'       => $this->integer(),
			'preventDefaultSwipeX' => $this->boolean()->defaultValue(true),
			'preventDefaultSwipeY' => $this->boolean()->defaultValue(false),
			'slideMargin'          => $this->integer(),
			'slideSelector'        => $this->string(),
			// Pager
			'pager'                => $this->boolean()->defaultValue(true),
			'pagerType'            => $this->string(),
			'pagerShortSeparator'  => $this->string(),
			'pagerSelector'        => $this->string(),
			'thumbnailPager'       => $this->boolean()->defaultValue(false),
			//Controls
			'controls'             => $this->boolean()->defaultValue(true),
			'nextText'             => $this->string(),
			'prevText'             => $this->string(),
			'nextSelector'         => $this->string(),
			'prevSelector'         => $this->string(),
			'autoControls'         => $this->boolean()->defaultValue(false),
			'startText'            => $this->string(),
			'stopText'             => $this->string(),
			'autoControlsCombine'  => $this->boolean()->defaultValue(false),
			'autoControlsSelector' => $this->string(),
			'keyboardEnabled'      => $this->boolean()->defaultValue(false),
			//Auto
			'auto'                 => $this->boolean()->defaultValue(false),
			'stopAutoOnClick'      => $this->boolean()->defaultValue(false),
			'pause'                => $this->integer(),
			'autoStart'            => $this->boolean()->defaultValue(false),
			'autoDirection'        => $this->string(),
			'autoHover'            => $this->boolean()->defaultValue(false),
			'autoDelay'            => $this->integer(),
			//Carousel
			'minSlides'            => $this->integer(),
			'maxSlides'            => $this->integer(),
			'moveSlides'           => $this->integer(),
			'slideWidth'           => $this->float(),
			'shrinkItems'          => $this->boolean()->defaultValue(false),
			//Develop
			'wrapperClass'         => $this->string(),
			'thumbClass'           => $this->string(),
			//
			'dateCreated'          => $this->dateTime()->notNull(),
			'dateUpdated'          => $this->dateTime()->notNull(),
			'uid'                  => $this->uid(),
		]);

		$this->createTable('{{%enupalslider_groups}}', [
			'id'                   => $this->primaryKey(),
			'name'                 => $this->string()->notNull(),
			'dateCreated'          => $this->dateTime()->notNull(),
			'dateUpdated'          => $this->dateTime()->notNull(),
			'uid'                  => $this->uid(),
		]);
	}

	/**
	 * Adds the foreign keys.
	 *
	 * @return void
	 */
	protected function addForeignKeys()
	{

		$this->addForeignKey(
			$this->db->getForeignKeyName(
				'{{%enupalslider_sliders}}', 'id'
			),
			'{{%enupalslider_sliders}}', 'id',
			'{{%elements}}', 'id', 'CASCADE', null
		);
	}
}