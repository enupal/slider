<?php
/**
 * EnupalSlider plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2017 Enupal
 */

namespace enupal\slider\controllers;

use Craft;
use craft\web\Controller as BaseController;
use craft\helpers\UrlHelper;
use yii\web\NotFoundHttpException;
use yii\db\Query;
use craft\helpers\ArrayHelper;
use craft\elements\Asset;

use enupal\slider\models\Settings;
use enupal\slider\Slider;
use enupal\slider\elements\Slider as SliderElement;

class SettingsController extends BaseController
{

    /*
     * Redirect to sliders settings
    */
    public function actionIndex()
    {
        return $this->renderTemplate('enupal-slider/settings/index');
    }

    /**
     * Save Plugin Settings
     *
     * @return \yii\web\Response
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveSettings()
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        $settings = $request->getBodyParam('settings');

        if (Slider::$app->settings->saveSettings($settings)) {
            Craft::$app->getSession()->setNotice(Slider::t('Settings saved.'));

            return $this->redirectToPostedUrl();
        } else {
            Craft::$app->getSession()->setError(Slider::t('Couldn’t save settings.'));

            // Send the settings back to the template
            craft()->urlManager->setRouteVariables([
                'settings' => $settings
            ]);
        }
    }
}
