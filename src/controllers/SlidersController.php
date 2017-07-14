<?php
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
	/*
	 * Redirect to sliders index page
	*/
	public function actionIndex()
	{
		return $this->renderTemplate('enupal-slider/sliders/index');
	}

	/**
	 * Save a slider
	 */
	public function actionSaveSlider()
	{
		$this->requirePostRequest();

		$request = Craft::$app->getRequest();
		$slider  = new SliderElement;

		$sliderId = $request->getBodyParam('sliderId');
		$isNew    = true;

		if ($sliderId)
		{
			$slider = Slider::$app->sliders->getSliderById($sliderId);

			if ($slider)
			{
				$isNew = false;
			}
		}

		//$slider->groupId     = $request->getBodyParam('groupId');
		$oldHandle              = $slider->handle;
		$newHandle              = $request->getBodyParam('handle');
		$slider->name           = $request->getBodyParam('name');
		$slider->handle         = $newHandle;
		$slider                 = Slider::$app->sliders->populateSliderFromPost($slider);

		// Save it
		if (!Slider::$app->sliders->saveSlider($slider))
		{
			Craft::$app->getSession()->setError(Slider::t('Couldn’t save slider.'));

			Craft::$app->getUrlManager()->setRouteParams([
					'slider'               => $slider
				]
			);

			return null;
		}

		//lets update the subfolder
		if (!$isNew && $oldHandle != $newHandle)
		{
			if (!Slider::$app->sliders->updateSubfolder($slider, $oldHandle))
			{
				Slider::log("Unable to rename subfolder {$oldHandle} to {$slider->handle}", 'error');
			}
		}

		Craft::$app->getSession()->setNotice(Slider::t('Slider saved.'));

		return $this->redirectToPostedUrl($slider);
	}

	/**
	 * Edit a Slider.
	 *
	 * @param int|null  $slierId The slider's ID, if editing an existing slider.
	 * @param SliderElement|null  $slider The slider send back by setRouteParams if any errors on saveSlider
	 *
	 * @throws HttpException
	 * @throws Exception
	 */
	public function actionEditSlider(int $sliderId = null, SliderElement $slider = null)
	{
		// Immediately create a new Slider
		if ($sliderId === null)
		{
			$slider = Slider::$app->sliders->createNewSlider();

			if ($slider->id)
			{
				$url = UrlHelper::cpUrl('enupal-slider/slider/edit/' . $slider->id);
				return $this->redirect($url);
			}
			else
			{
				throw new Exception(Craft::t('Error creating Slider'));
			}
		}
		else
		{
			if ($sliderId !== null)
			{
				if ($slider === null)
				{
					$variables['groups']  = Slider::$app->groups->getAllSlidersGroups();
					$variables['groupId'] = "";

					// Get the Slider
					$slider = Slider::$app->sliders->getSliderById($sliderId);

					if (!$slider)
					{
						throw new NotFoundHttpException(Slider::t('Slider not found'));
					}
				}
			}
		}

		$sources = Slider::$app->sliders->getVolumeFolder($slider);

		$variables['sources']  = $sources;
		$variables['sliderId'] = $sliderId;
		$variables['slider']   = $slider;
		$variables['name']     = $slider->name;
		$variables['groupId']  = $slider->groupId;
		$variables['elementType'] = Asset::class;

		$variables['slidesElements']  = null;

		if ($slider->slides)
		{
			$slides = $slider->slides;
			if (is_string($slides))
			{
				$slides = json_decode($slider->slides);
			}

			$slidesElements = [];

			if (count($slides))
			{
				foreach ($slides as $key => $slideId)
				{
					$slide = Craft::$app->elements->getElementById($slideId);
					array_push($slidesElements, $slide);
				}

				$variables['slidesElements'] = $slidesElements;
			}
		}

		$variables['showPreviewBtn'] = false;
		// Enable Live Preview?
		if (!Craft::$app->getRequest()->isMobileBrowser(true))
		{

			//#title-field, #fields > div > div > .field
			$this->getView()->registerJs('Craft.LivePreview.init('.Json::encode([
					'fields' => '.field',
					'previewAction' => 'enupal-slider/sliders/live-preview',
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
	 */
	public function actionDeleteSlider()
	{
		$this->requirePostRequest();

		$request = Craft::$app->getRequest();

		$sliderId = $request->getRequiredBodyParam('id');
		$slider   = Slider::$app->sliders->getSliderById($sliderId);

		// @TODO - handle errors
		$success = Slider::$app->sliders->deleteSlider($slider);

		return $success;
	}

	/**
	 * Live preview.
	 *
	 * @return void
	 */
	public function actionLivePreview(): Response
	{
		$this->requirePostRequest();
		$slider = new SliderElement;

		$slider         = Slider::$app->sliders->populateSliderFromPost($slider);
		$sliderOptions  = Slider::$app->sliders->getDefaultOptionsByAjax($slider);
		$slidesElements = [];
		$settings       = Slider::$app->sliders->getSettings();
		$sliderHtml     = null;
		$templatePath   = Slider::$app->sliders->getEnupalSliderPath();
		$dataAttributes = Slider::$app->sliders->getDataAttributes($slider);

		foreach ($slider->slides as $key => $slideId)
		{
			$slide = Craft::$app->elements->getElementById($slideId);
			array_push($slidesElements, $slide);
		}

		$this->getView()->getTwig()->disableStrictVariables();
		$this->getView()->registerAssetBundle('enupal\\slider\\assetbundles\\SliderAsset');
		$this->getView()->registerAssetBundle('enupal\\slider\\assetbundles\\LivePreviewAsset');
		// set path
		$templatePath = Craft::getAlias('@enupal/slider/templates/');
		$originalTemplatesPath = Craft::$app->getView()->getTemplatesPath();

		Craft::$app->getView()->setTemplatesPath($templatePath);

		$rendered = $this->renderTemplate('_preview/index', [
			'slider'         => $slider,
			'slidesElements' => $slidesElements,
			'dataAttributes' => $dataAttributes,
			'htmlHandle'     => $settings['htmlHandle'],
			'linkHandle'     => $settings['linkHandle'],
			'openLinkHandle' => $settings['openLinkHandle'],
			'options'        => []
		]);

		Craft::$app->getView()->setTemplatesPath($originalTemplatesPath);

		return $rendered;
	}
}
