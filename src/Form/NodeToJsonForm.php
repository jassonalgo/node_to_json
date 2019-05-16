<?php

namespace Drupal\node_to_json\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class NodeToJsonForm extends ConfigFormBase {

	public function getFormId() {
		return 'node_to_json_form';
	}

	public function buildForm(array $form, FormStateInterface $form_state) {
		// Form constructor.
		$form = parent::buildForm($form, $form_state);
		// Default settings.
		$config = $this->config('node_to_json.settings');
		// Page title field.
		$form['path'] = array(
			'#type' => 'textfield',
			'#title' => $this->t('Location path:'),
			'#default_value' => $config->get('node_to_json.path'),
			'#description' => $this->t('the location were the files are be loacted'),
		);

		return $form;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validateForm(array &$form, FormStateInterface $form_state) {

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
