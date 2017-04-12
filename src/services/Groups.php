<?php
namespace enupal\slider\services;

use Craft;
use yii\base\Component;

use enupal\slider\Slider;
use enupal\slider\elements\Slider as SliderElement;
use enupal\slider\models\SliderGroup as SliderGroupModel;
use enupal\slider\records\SliderGroup as SliderGroupRecord;

class Groups extends Component
{
	private $_groupsById;
	private $_fetchedAllGroups = false;

	/**
	 * Saves a group
	 *
	 * @param SliderGroupModel $group
	 *
	 * @return bool
	 */
	public function saveGroup(SliderGroupModel $group): bool
	{
		$groupRecord       = $this->_getGroupRecord($group);
		$groupRecord->name = $group->name;

		if ($groupRecord->validate())
		{
			$groupRecord->save(false);

			// Now that we have an ID, save it on the model & models
			if (!$group->id)
			{
				$group->id = $groupRecord->id;
			}

			return true;
		}
		else
		{
			$group->addErrors($groupRecord->getErrors());

			return false;
		}
	}

	/**
	 * Deletes a group
	 *
	 * @param int $groupId
	 *
	 * @return bool
	 */
	public function deleteGroupById($groupId)
	{
		$groupRecord = SliderGroupModel::findOne($groupId);

		if (!$groupRecord)
		{
			return false;
		}

		$affectedRows = Craft::$app->getDb()
			->createCommand()
			->delete('{{%enupalslider_groups}}', ['id' => $groupId])
			->execute();

		return (bool) $affectedRows;
	}

	/**
	 * Returns all groups.
	 *
	 * @param string|null $indexBy
	 *
	 * @return array
	 */
	public function getAllSlidersGroups($indexBy = null)
	{
		if (!$this->_fetchedAllGroups)
		{
			$groupRecords = SliderGroupRecord::find()
				->orderBy(['name' => SORT_ASC])
				->all();

			foreach ($groupRecords as $key => $groupRecord)
			{
				$groupRecords[$key] = new SliderGroupRecord($groupRecord);
			}

			$this->_groupsById       = $groupRecords;
			$this->_fetchedAllGroups = true;
		}

		if ($indexBy == 'id')
		{
			$groups = $this->_groupsById;
		}
		else
		{
			if (!$indexBy)
			{
				$groups = array_values($this->_groupsById);
			}
			else
			{
				$groups = array();
				foreach ($this->_groupsById as $group)
				{
					$groups[$group->$indexBy] = $group;
				}
			}
		}

		return $groups;
	}

	/**
	 * Get Sliders by Group ID
	 *
	 * @param  int $groupId
	 *
	 * @return SliderElement
	 */
	public function getSlidersByGroupId($groupId)
	{
		$query = Craft::$app->getDb()
			->createCommand()
			->from('{{%enupalslider_groups}}')
			->where('groupId=:groupId', ['groupId' => $groupId])
			->order('name')
			->all();

		foreach ($query as $key => $value)
		{
			$query[$key] = new SliderElement($value);
		}

		return $query;
	}

	/**
	 * Gets a slider group record or creates a new one.
	 *
	 * @access private
	 *
	 * @param SliderGroupModel $group
	 *
	 * @throws Exception
	 * @return SliderGroupModel
	 */
	private function _getGroupRecord(SliderGroupModel $group)
	{
		if ($group->id)
		{
			$groupRecord = SliderGroupModel::findOne($group->id);

			if (!$groupRecord)
			{
				throw new Exception(
					Slider::t(
						'No field group exists with the ID '.$group->id
					)
				);
			}
		}
		else
		{
			$groupRecord = new SliderGroupModel();
		}

		return $groupRecord;
	}
}
