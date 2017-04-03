<?php
namespace enupal\slider\records;

use Craft;
use craft\db\ActiveRecord;
use yii\db\ActiveQueryInterface;
use craft\records\Element;


/**
 * Class Slider record.
 *
 * @property int         $id
 * @property int         $groupId
 * @property string      $name
 * @property string      $handle
 */
class Slider extends ActiveRecord
{
	/**
	 * @inheritdoc
	 *
	 * @return string
	 */
	public static function tableName(): string
	{
		return '{{%enupalslider_sliders}}';
	}

	/**
	 * Returns the entry’s element.
	 *
	 * @return ActiveQueryInterface The relational query object.
	 */
	public function getElement(): ActiveQueryInterface
	{
		return $this->hasOne(Element::class, ['id' => 'id']);
	}

	/**
	 * Returns the form’s group.
	 *
	 * @return ActiveQueryInterface The relational query object.

	public function getGroup(): ActiveQueryInterface
	{
		return $this->hasOne(FormGroup::class, ['id' => 'groupId']);
	}*/


}