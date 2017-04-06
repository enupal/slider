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
	 * Save a form
	 */
	public function actionSaveSlider()
	{
		$this->requirePostRequest();

		$request = Craft::$app->getRequest();
		$form    = new SliderElement();

		if ($request->getBodyParam('saveAsNew'))
		{
			$form->saveAsNew = true;
			$duplicateForm = Slider::$app()->sliders->createNewForm(
				$request->getBodyParam('name'),
				$request->getBodyParam('handle')
			);

			if ($duplicateForm)
			{
				$form->id = $duplicateForm->id;
			}
			else
			{
				throw new Exception(Craft::t('Error creating Form'));
			}
		}
		else
		{
			$form->id = $request->getBodyParam('id');
		}

		$form->groupId              = $request->getBodyParam('groupId');
		$form->name                 = $request->getBodyParam('name');
		$form->handle               = $request->getBodyParam('handle');
		$form->titleFormat          = $request->getBodyParam('titleFormat');
		$form->displaySectionTitles = $request->getBodyParam('displaySectionTitles');
		$form->redirectUri          = $request->getBodyParam('redirectUri');
		$form->submitAction         = $request->getBodyParam('submitAction');
		$form->savePayload          = $request->getBodyParam('savePayload', 0);
		$form->submitButtonText     = $request->getBodyParam('submitButtonText');

		$form->notificationEnabled      = $request->getBodyParam('notificationEnabled');
		$form->notificationRecipients   = $request->getBodyParam('notificationRecipients');
		$form->notificationSubject      = $request->getBodyParam('notificationSubject');
		$form->notificationSenderName   = $request->getBodyParam('notificationSenderName');
		$form->notificationSenderEmail  = $request->getBodyParam('notificationSenderEmail');
		$form->notificationReplyToEmail = $request->getBodyParam('notificationReplyToEmail');
		$form->enableTemplateOverrides  = $request->getBodyParam('enableTemplateOverrides', 0);
		$form->templateOverridesFolder  = $form->enableTemplateOverrides
			? $request->getBodyParam('templateOverridesFolder')
			: null;
		$form->enableFileAttachments    = $request->getBodyParam('enableFileAttachments');

		// Set the field layout
		$fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();

		if ($form->saveAsNew)
		{
			$fieldLayout = Slider::$app->fields->getDuplicateLayout($duplicateForm, $fieldLayout);
		}

		$fieldLayout->type = Form::class;
		$form->setFieldLayout($fieldLayout);

		// Delete any fields removed from the layout
		$deletedFields = $request->getBodyParam('deletedFields');

		if (count($deletedFields) > 0)
		{
			// Backup our field context and content table
			$oldFieldContext = Craft::$app->content->fieldContext;
			$oldContentTable = Craft::$app->content->contentTable;

			// Set our field content and content table to work with our form output
			Craft::$app->content->fieldContext = $form->getFieldContext();
			Craft::$app->content->contentTable = $form->getContentTable();

			$currentTitleFormat = null;

			foreach ($deletedFields as $fieldId)
			{
				// Each field deleted will be update the titleFormat
				$currentTitleFormat = Slider::$app->sliders->cleanTitleFormat($fieldId);
				Craft::$app->fields->deleteFieldById($fieldId);
			}

			if ($currentTitleFormat)
			{
				// update the titleFormat
				$form->titleFormat = $currentTitleFormat;
			}

			// Reset our field context and content table to what they were previously
			Craft::$app->content->fieldContext = $oldFieldContext;
			Craft::$app->content->contentTable = $oldContentTable;
		}

		// Save it
		if (!Slider::$app->sliders->saveForm($form))
		{
			Craft::$app->getSession()->setError(Slider::t('Couldn’t save form.'));

			$notificationFields = [
				'notificationRecipients',
				'notificationSubject',
				'notificationSenderName',
				'notificationSenderEmail',
				'notificationReplyToEmail'
			];

			$notificationErrors = false;
			foreach ($form->getErrors() as $fieldHandle => $error)
			{
				if (in_array($fieldHandle, $notificationFields))
				{
					$notificationErrors = 'error';
					break;
				}
			}

			Craft::$app->getUrlManager()->setRouteParams([
					'form'               => $form,
					'notificationErrors' => $notificationErrors
				]
			);

			return null;
		}

		Craft::$app->getSession()->setNotice(Slider::t('Form saved.'));

		#$_POST['redirect'] = str_replace('{id}', $form->id, $_POST['redirect']);

		return $this->redirectToPostedUrl($form);
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
				$variables['groups']  = Slider::$app->groups->getAllFormGroups();
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
