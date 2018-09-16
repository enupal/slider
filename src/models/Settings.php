<?php
/**
 * EnupalSlider plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2017 Enupal
 */

namespace enupal\slider\models;

class Settings extends \craft\base\Model
{
    public $pluginNameOverride = '';
    public $volumeId = '';
    public $linkHandle = '';
    public $openLinkHandle = '';
    public $htmlHandle = '';
    public $loadJquery = 1;
}