<?php
namespace enupal\slider\controllers;

use Craft;
use craft\web\Controller as BaseController;
use craft\helpers\UrlHelper;
use yii\web\NotFoundHttpException;
use craft\elements\Asset;

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
		$slider  = new SliderElement();

		/*if ($request->getBodyParam('saveAsNew'))
		{
			@todo save as new feature
			$slider->saveAsNew = true;
			$duplicateForm = Slider::$app()->sliders->createNewForm(
				$request->getBodyParam('name'),
				$request->getBodyParam('handle')
			);

			if ($duplicateForm)
			{
				$slider->id = $duplicateForm->id;
			}
			else
			{
				throw new Exception(Craft::t('Error creating Form'));
			}
		}*/

		$slider->id = $request->getBodyParam('id');

		//$slider->groupId     = $request->getBodyParam('groupId');
		$slider->name        = $request->getBodyParam('name');
		$slider->handle      = $request->getBodyParam('handle');
		$slider->slides      = $request->getBodyParam('slides');

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

		Craft::$app->getSession()->setNotice(Slider::t('Slider saved.'));

		#$_POST['redirect'] = str_replace('{id}', $form->id, $_POST['redirect']);

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
		else
		{
			$slider = new SliderElement;
		}

		$variables['sliderId'] = $sliderId;
		$variables['slider']   = $slider;
		$variables['name']     = $slider->name;
		$variables['groupId']  = $slider->groupId;
		$variables['elementType'] = Asset::class;
		$variables['slidesElements']  = null;
		$variables['slideElementId']  = null;

		if ($slider->slides)
		{
			$slides = json_decode($slider->slides);
			$slidesElements = [];

			if (count($slides))
			{
				$variables['slideElementId'] = $slides[0];

				foreach ($slides as $key => $slideId)
				{
					$slide = Craft::$app->elements->getElementById($slideId);
					array_push($slidesElements, $slide);
				}

				$variables['slidesElements'] = $slidesElements;
			}

		}

		// Set the "Continue Editing" URL
		$variables['continueEditingUrl'] = 'enupalslider/slider/edit/{id}';

		$variables['settings'] = Craft::$app->plugins->getPlugin('enupalslider')->getSettings();

		return $this->renderTemplate('enupalslider/sliders/_editSlider', $variables);
	}

	/**
	 * Delete a form.
	 *
	 * @return void
	 */
	public function actionDeleteForm()
	{
		$this->requirePostRequest();

		$request = Craft::$app->getRequest();

		// Get the Form these fields are related to
		$formId = $request->getRequiredBodyParam('id');
		$form   = Slider::$app->sliders->getFormById($formId);

		// @TODO - handle errors
		$success = Slider::$app->sliders->deleteForm($form);

		return $this->redirectToPostedUrl($form);
	}
}
