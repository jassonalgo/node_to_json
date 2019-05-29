<?php

namespace Drupal\node_to_json\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class NodeToJsonForm extends ConfigFormBase {

	public function getFormId() {
		return 'node_to_json_form';
	}

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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function submitForm(array &$form, FormStateInterface $form_state) {
		$config = $this->config('node_to_json.settings');
		$config->set('node_to_json.path', $form_state->getValue('path'));
		$config->save();
		return parent::submitForm($form, $form_state);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getEditableConfigNames() {
		return [
			'node_to_json.settings',
		];
	}
}
?>
