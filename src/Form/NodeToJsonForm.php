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
		$listFields = \Drupal::service('entity_field.manager')->getFieldDefinitions('node', 'static_pages');

		$node_field_map = $field_map['node'];
		$node_fields = array_keys($node_field_map['node']);
		$field_map = \Drupal::entityManager()->getFieldMap();
		$node_field_map = $field_map['node'];
		$node_fields = array_keys($node_field_map['static_pages']);
		$node_article_fields = \Drupal::entityManager()->getFieldDefinitions('node', 'article');
		//dpm($node_article_fields);
		//$node = Node::create(['type' => 'static_pages']);
		//dpm($node->getFieldDefinitions());
		//
		if (isset($definitions[$field_name])) {
			$options_array = $definitions[$field_name]->getSetting('allowed_values');
		}
		$entity_type_id = 'node';
		$bundle = 'article';
		foreach (\Drupal::entityManager()->getFieldDefinitions($entity_type_id, $bundle) as $field_name => $field_definition) {
			dpm(get_class_methods($field_definition));
			if (!empty($field_definition->getTargetBundle())) {
				$bundleFields[$entity_type_id][$field_name]['type'] = $field_definition->getType();
				$bundleFields[$entity_type_id][$field_name]['label'] = $field_definition->getLabel();
			}
		}

		//dpm($ref_fields);
		//dpm($listFields);
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
			'#description' => $this->t('the location were the files are be loacted not be abosolute'),
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
			drupal_set_message(t("the folder is ok"), 'status');
		} else {
			drupal_set_message(t("something is bad"), 'status');
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
