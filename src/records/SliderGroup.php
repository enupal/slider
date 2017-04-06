<?php
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