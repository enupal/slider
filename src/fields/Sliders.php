<?php

namespace enupal\slider\fields;

use craft\base\ElementInterface;
use craft\fields\BaseRelationField;
use enupal\slider\elements\Slider;
use enupal\slider\Slider as SliderPlugin;

/**
 * Class Sliders
 *
 */
class Sliders extends BaseRelationField
{
    /**
     * @inheritdoc
     */
    public $allowMultipleSources = false;

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return SliderPlugin::t('Sliders');
    }

    /**
     * @inheritdoc
     */
    protected static function elementType(): string
    {
        return Slider::class;
    }

    /**
     * @inheritdoc
     */
    public static function defaultSelectionLabel(): string
    {
        return SliderPlugin::t('Add a Slider');
    }
}
