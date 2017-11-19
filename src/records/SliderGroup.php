<?php
/**
 * EnupalSlider plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2017 Enupal
 */

namespace enupal\slider\records;

use Craft;
use craft\db\ActiveRecord;
use yii\db\ActiveQueryInterface;

/**
 * Class SliderGroup record.
 *
 * @property int    $id    ID
 * @property string $name  Name
 */
class SliderGroup extends ActiveRecord
{
	/**
	 * @inheritdoc
	 *
	 * @return string
	 */
	public static function tableName(): string
	{
		return '{{%enupalslider_groups}}';
	}

}