<?php

namespace enupal\slider\migrations;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\db\Table;
use craft\helpers\Json;
use craft\services\Plugins;
use enupal\slider\Slider;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m190205_000000_craft31_update extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $plugin = Slider::getInstance();
        $settings = $plugin->getSettings();

        if ($settings->volumeId){
            $volume = (new Query())
                ->select(['id', 'uid'])
                ->from([Table::VOLUMES])
                ->where(['id' => $settings->volumeId])
                ->one();

            $settings->volumeUid = $volume['uid'];

            $projectConfig = Craft::$app->getProjectConfig();
            $projectConfig->set(\craft\services\ProjectConfig::PATH_PLUGINS . '.' . $plugin->handle . '.settings', $settings->toArray());
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190205_000000_craft31_update cannot be reverted.\n";

        return false;
    }
}
