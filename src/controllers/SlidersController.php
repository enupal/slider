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
use craft\helpers\Json;
use craft\helpers\Template as TemplateHelper;
use yii\web\Response;

use enupal\slider\variables\SliderVariable;
use enupal\slider\Slider;
use enupal\slider\elements\Slider as SliderElement;

class SlidersController extends BaseController
{
    /**
     * Save a slider
     */
    public function actionSaveSlider()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $slider = new SliderElement;

        $sliderId = $request->getBodyParam('sliderId');
        $isNew = true;

        if ($sliderId) {
            $slider = Slider::$app->sliders->getSliderById($sliderId);

            if ($slider) {
                $isNew = false;
            }
        }

        //$slider->groupId     = $request->getBodyParam('groupId');
        $oldHandle = $slider->handle;
        $slider = Slider::$app->sliders->populateSliderFromPost($slider);
        $newHandle = $slider->handle;

        // Save it
        if (!Slider::$app->sliders->saveSlider($slider)) {
            Craft::$app->getSession()->setError(Slider::t('Couldn’t save slider.'));

            Craft::$app->getUrlManager()->setRouteParams([
                    'slider' => $slider
                ]
            );

            return null;
        }

        //lets update the subfolder
        if (!$isNew && $oldHandle != $newHandle) {
            if (!Slider::$app->sliders->updateSubfolder($slider, $oldHandle)) {
                Slider::log("Unable to rename subfolder {$oldHandle} to {$slider->handle}", 'error');
            }
        }

        Craft::$app->getSession()->setNotice(Slider::t('Slider saved.'));

        return $this->redirectToPostedUrl($slider);
    }

    /**
     * Edit a Slider.
     *
     * @param int|null $sliderId
     * @param SliderElement|null $slider The slider send back by setRouteParams if any errors on saveSlider
     *
     * @return Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     */
    public function actionEditSlider(int $sliderId = null, SliderElement $slider = null)
    {
        $redactor = Craft::$app->plugins->getPlugin('redactor');
        // Immediately create a new Slider
        if ($sliderId === null) {
            $slider = Slider::$app->sliders->createNewSlider();

            if ($slider->id) {
                $url = UrlHelper::cpUrl('enupal-slider/slider/edit/'.$slider->id);
                return $this->redirect($url);
            } else {
                throw new \Exception(Craft::t('enupal-slider','Error creating Slider'));
            }
        } else {
            if ($sliderId !== null) {
                if ($slider === null) {
                    $variables['groups'] = Slider::$app->groups->getAllSlidersGroups();
                    $variables['groupId'] = "";

                    // Get the Slider
                    $slider = Slider::$app->sliders->getSliderById($sliderId);

                    if (!$slider) {
                        throw new NotFoundHttpException(Slider::t('Slider not found'));
                    }
                }
            }
        }

        $sources = Slider::$app->sliders->getVolumeFolder($slider);

        $variables['sources'] = $sources;
        $variables['sliderId'] = $sliderId;
        $variables['slider'] = $slider;
        $variables['name'] = $slider->name;
        $variables['groupId'] = $slider->groupId;
        $variables['elementType'] = Asset::class;

        $variables['slidesElements'] = null;

        if ($slider->slides) {
            $slides = $slider->slides;
            if (is_string($slides)) {
                $slides = json_decode($slider->slides);
            }

            $slidesElements = [];

            if (count($slides)) {
                foreach ($slides as $key => $slideId) {
                    $slide = Craft::$app->elements->getElementById($slideId);
                    array_push($slidesElements, $slide);
                }

                $variables['slidesElements'] = $slidesElements;
            }
        }

        $variables['showPreviewBtn'] = false;
        // Enable Live Preview?
        if (!Craft::$app->getRequest()->isMobileBrowser(true)) {
            //#title-field, #fields > div > div > .field
            $this->getView()->registerJs('Craft.LivePreview.init('.Json::encode([
                    'fields' => '#fields-tab-enupalslider-settings .field, #fields-tab-enupalslider-controls .field, #fields-tab-enupalslider-slides .field, #fields-slides-field',
                    'previewAction' => Craft::$app->getSecurity()->hashData('enupal-slider/sliders/live-preview'),
                    'previewParams' => [
                        'sliderId' => $slider->id
                    ]
                ]).');');

            $variables['showPreviewBtn'] = true;
        }

        // Set the "Continue Editing" URL
        $variables['continueEditingUrl'] = 'enupal-slider/slider/edit/{id}';

        $variables['settings'] = Craft::$app->plugins->getPlugin('enupal-slider')->getSettings();

        return $this->renderTemplate('enupal-slider/sliders/_editSlider', $variables);
    }

    /**
     * Delete a slider.
     *
     * @return void
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDeleteSlider()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $sliderId = $request->getRequiredBodyParam('id');
        $slider = Slider::$app->sliders->getSliderById($sliderId);

        // @TODO - handle errors
        $success = Slider::$app->sliders->deleteSlider($slider);

        return $success;
    }

    /**
     * Live preview.
     *
     * @return Response
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionLivePreview(): Response
    {
        $this->requirePostRequest();
        $slider = new SliderElement;

        $slider = Slider::$app->sliders->populateSliderFromPost($slider);
        $sliderOptions = Slider::$app->sliders->getDefaultOptionsByAjax($slider);
        $slidesElements = [];
        $settings = Slider::$app->settings->getSettings();
        $sliderHtml = null;
        $templatePath = Slider::$app->sliders->getEnupalSliderPath();
        $dataAttributes = Slider::$app->sliders->getDataAttributes($slider);

        foreach ($slider->slides as $key => $slideId) {
            $slide = Craft::$app->elements->getElementById($slideId);
            array_push($slidesElements, $slide);
        }

        $this->getView()->getTwig()->disableStrictVariables();
        // set path
        $templatePath = Craft::getAlias('@enupal/slider/templates/');
        $originalTemplatesPath = Craft::$app->getView()->getTemplatesPath();

        Craft::$app->getView()->setTemplatesPath($templatePath);

        $rendered = $this->renderTemplate('_preview/index', [
            'slider' => $slider,
            'slidesElements' => $slidesElements,
            'dataAttributes' => $dataAttributes,
            'htmlHandle' => $settings['htmlHandle'],
            'linkHandle' => $settings['linkHandle'],
            'openLinkHandle' => $settings['openLinkHandle'],
            'options' => []
        ]);

        Craft::$app->getView()->setTemplatesPath($originalTemplatesPath);

        return $rendered;
    }

    /*
     * Download Default Image
    */
    public function actionDownloadDefault()
    {
        $this->requirePostRequest();

        $date = date('Y-m-d_H_i_s');
        $imagePath = Craft::getAlias('@enupal/slider/resources/default.png');
        $tempPath = Craft::$app->getPath()->getTempPath().DIRECTORY_SEPARATOR.pathinfo('Default_'.$date, PATHINFO_FILENAME).'.png';

        copy($imagePath, $tempPath);

        if (!is_file($tempPath)) {
            throw new NotFoundHttpException(Backup::t('Invalid default image name: {filename}', [
                'filename' => $tempPath
            ]));
        }

        return Craft::$app->getResponse()->sendFile($tempPath);
    }
}
