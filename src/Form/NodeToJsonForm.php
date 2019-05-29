<?php

namespace Drupal\node_to_json\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements the NodeToJsonForm form controller.
 *
 * This class create a admin form, to NodeToJson module.
 *
 * @see \Drupal\Core\Form\ConfigFormBase
 */
class NodeToJsonForm extends ConfigFormBase {

	/**
	 * Getter method for Form ID.
	 *
	 * The form ID is used in implementations of hook_form_alter() to allow other
	 * modules to alter the render array built by this form controller. It must be
	 * unique site wide. It normally starts with the providing module's name.
	 *
	 * @return string
	 *   The unique ID of the form defined by this class.
	 */
	public function getFormId() {
		return 'node_to_json_form';
	}

	/**
	 * Build the simple form.
	 *
	 * A build form method constructs an array that defines how markup and
	 * other form elements are included in an HTML form.
	 *
	 * @param array $form
	 *   Default form array structure.
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *   Object containing current form state.
	 *
	 * @return array
	 *   The render array defining the elements of the form.
	 */
	public function buildForm(array $form, FormStateInterface $form_state) {
		//get content type list
		$list = node_type_get_types();
		dpm(array_keys($list));

		// Form constructor.
		$form = parent::buildForm($form, $form_state);
		// Default settings.
		$config = $this->config('node_to_json.settings');
		// Page title field.
		$form['path'] = array(
			'#type' => 'textfield',
			'#title' => $this->t('Location path:'),
			'#default_value' => $config->get('node_to_json.path'),
			'#description' => $this->t('there is a problem it is necessary to review the permits'),
			'#required' => TRUE,
		);

		return $form;
	}

/**
 * Implements form validation.
 *
 * The validateForm method is the default method called to validate input on
 * a form.
 *
 * @param array $form
 *   The render array of the currently built form.
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   Object describing the current state of the form.
 */
	public function validateForm(array &$form, FormStateInterface $form_state) {
		//validate the directory
		$path = "public://" . $form_state->getValue('path');
		if (file_prepare_directory($path, FILE_CREATE_DIRECTORY)) {
			drupal_set_message(t("The folder is valid"), 'status');
		} else {
			$form_state->setErrorByName('path', $this->t(''));
		}

	}

	/**
	 * Implements a form submit handler.
	 *
	 * The submitForm method is the default method called for any submit elements.
	 *
	 * @param array $form
	 *   The render array of the currently built form.
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *   Object describing the current state of the form.
	 */
	public function submitForm(array &$form, FormStateInterface $form_state) {
		//get config module
		$config = $this->config('node_to_json.settings');
		$config->set('node_to_json.path', $form_state->getValue('path'));
		$config->save();
		return parent::submitForm($form, $form_state);
	}

	/**
	 * Gets the configuration names that will be editable.
	 *
	 * @return array
	 *   An array of configuration object names that are editable if called in
	 *   conjunction with the trait's config() method.
	 */
	protected function getEditableConfigNames() {
		return [
			'node_to_json.settings',
		];
	}
}
?>
