<?php
namespace enupal\slider\controllers;

use Craft;
use craft\web\Controller as BaseController;
use craft\helpers\UrlHelper;
use yii\web\NotFoundHttpException;
use yii\db\Query;
use craft\helpers\ArrayHelper;
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
		$slider  = new SliderElement;

		// @todo - save as new
		/*if ($request->getBodyParam('saveAsNew'))
		{
			@todo save as new feature
			$slider->saveAsNew = true;
			$duplicateSlider = Slider::$app()->sliders->createNewSlider(
				$request->getBodyParam('name'),
				$request->getBodyParam('handle')
			);

			if ($duplicateSlider)
			{
				$slider->id = $duplicateSlider->id;
			}
			else
			{
				throw new Exception(Craft::t('Error creating Form'));
			}
		}*/

		$sliderId = $request->getBodyParam('sliderId');

		if ($sliderId)
		{
			$slider    = Slider::$app->sliders->getSliderById($sliderId);
			$oldHandle = $request->getBodyParam('handle');
			if ($slider)
			{
				//lets update the subfolder
				if ($slider->handle != $oldHandle)
				{
					if (!Slider::$app->sliders->updateSubfolder($slider, $oldHandle))
					{
						Slider::log("Unable to rename subfolder {$oldHandle} to {$slider->handle}", 'error');
					}
				}
			}
		}

		//$slider->groupId     = $request->getBodyParam('groupId');
		$slider->name        = $request->getBodyParam('name');
		$slider->handle      = $request->getBodyParam('handle');
		$slider->slides      = $request->getBodyParam('slides');
		$slider->mode        = $request->getBodyParam('mode');

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
		// Immediately create a new Form
		if ($sliderId === null)
		{
			$slider = Slider::$app->sliders->createNewSlider();

			if ($slider->id)
			{
				$url = UrlHelper::cpUrl('enupalslider/slider/edit/' . $slider->id);
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

		$settings = (new Query())
			->select('settings')
			->from(['{{%plugins}}'])
			->where(['handle' => 'enupalslider'])
			->one();

		$sources = null;
		$settings = json_decode($settings['settings'], true);

		if (isset($settings['volumeId']))
		{
			$folder = (new Query())
			->select('*')
			->from(['{{%volumefolders}}'])
			->where(['volumeId' => $settings['volumeId']])
			->one();

			$sources = ['folder:'.$folder['id']];

			$subFolder = (new Query())
			->select('*')
			->from(['{{%volumefolders}}'])
			->where([
					'volumeId' => $settings['volumeId'],
					'parentId' => $folder['id'],
					'name' => $slider->handle])
			->one();

			if ($subFolder)
			{
				$sources = ['folder:'.$folder['id'].'/folder:'.$subFolder['id']];
			}
		}

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
