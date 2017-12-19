<?php

namespace craft\migrations;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;
use enupal\slider\Slider;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m171219_000000_enupalslider_addAssetTransforms extends Migration
{
	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{
		$tableName = '{{%enupalslider_sliders}}';
		$assetTransform = 'assetTransform';
		$thumbTransform = 'thumbTransform';

		if (!$this->db->columnExists($tableName, $assetTransform))
		{
			$this->addColumn($tableName, $assetTransform, 'string AFTER `thumbClass`');
			Slider::log("Added assetTransform column");
		}

		if (!$this->db->columnExists($tableName, $thumbTransform))
		{
			$this->addColumn($tableName, $thumbTransform, 'string AFTER `thumbClass`');
			Slider::log("Added thumbTransform column");
		}

		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		echo "m171219_000000_enupalslider_addAssetTransforms cannot be reverted.\n";

		return false;
	}
}
