<?php
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
		$this->dropTable('{{%enupalslider_sliders}}');
		$this->dropTable('{{%enupalslider_groups}}');
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
			'infiniteLoop'         => $this->boolean()->defaultValue(true),
			'hasCaptions'          => $this->boolean()->defaultValue(true),
			'isTicker'             => $this->boolean()->defaultValue(false),
			'tickerHover'          => $this->boolean()->defaultValue(false),
			'adaptiveHeight'       => $this->boolean()->defaultValue(false),
			'adaptiveHeightSpeed'  => $this->integer(),
			'hasVideo'             => $this->boolean()->defaultValue(false),
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
			'dateCreated'          => $this->dateTime()->notNull(),
			'dateUpdated'          => $this->dateTime()->notNull(),
			'uid'                  => $this->uid(),
		]);

		$this->createTable('{{%enupalslider_groups}}', [
			'id'                       => $this->primaryKey(),
			'name'                     => $this->string()->notNull(),
			'dateCreated'              => $this->dateTime()->notNull(),
			'dateUpdated'              => $this->dateTime()->notNull(),
			'uid'                      => $this->uid(),
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