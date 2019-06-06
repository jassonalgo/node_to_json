<?php

namespace Drupal\node_to_json\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements the NodeToJsonForm form controller.
 *
 * This class create a admin form, to NodeToJson module.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class NodeToJsonForm extends FormBase {

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
		//get config
		$config = $this->config('node_to_json.settings');
		$avalibleContentTypes = $config->get('node_to_json.fields_avalible');
		$configContentTypes = $config->get('node_to_json.content');
		//dpm($configContentTypes);
		//get content type list
		$list = $this->contentTypeFields();

		$form['list_content_type'] = [
			'#type' => 'item',
			'#prefix' => '<div class="bold" >',
			'#suffix' => '</div>',
			'#markup' => $this->t('List of the content types.'),
		];
		//print the content types in form
		foreach ($list as $key => $value) {
			$defaultValue = 0;
			//verify if content type is checked for create json
			if (array_key_exists($key, $configContentTypes)) {
				$defaultValue = 1;
			}
			$form['node_to_json---' . $key] = array(
				'#type' => 'checkbox',
				'#title' => $value['label'],
				'#attributes' => array('class' => array('content-type', $key), 'data-content' => $key),
				'#default_value' => $defaultValue,
			);
			//fieldset whit field of the contetn type
			$form[$key . '_fieldset'] = [
				'#type' => 'fieldset',
				'#attributes' => array('class' => array('content-type-fields', 'hide', $key)),
				'#title' => $this->t(
					'fields of @content', array('@content' => $value['label'])
				),
			];
			//go over arrays whit fields of content type
			foreach ($value['fields'] as $key2 => $value2) {
				if (in_array($value2['type'], $avalibleContentTypes)) {
					$defaultValue = 0;
					$defaultValueFileName = 'nid';
					if (array_key_exists('file_name', $configContentTypes[$key])) {
						$defaultValueFileName = $configContentTypes[$key]['file_name'];
					}
					//verify if field type is checked for add in  create json
					$form[$key . '_fieldset']['name']['node_to_json---' . $key . '---filename'] = [
						'#type' => 'select',
						'#title' => $this->t('Chose whoe determinate the name of the file'),
						'#options' => [
							'nid' => $this->t('Node id'),
							'title' => $this->t('Node title'),
							//'custom' => $this->t('Custom string field'),
						],
						'#default_value' => $defaultValueFileName,
						'#empty_option' => $this->t('-select-'),
					];
					if (array_key_exists($key, $configContentTypes) && in_array($key2, $configContentTypes[$key]['fields'])) {
						$defaultValue = 1;
					}
					//print avalible fields
					$form[$key . '_fieldset']['name']['node_to_json---' . $key . '---' . $key2] = array(
						'#type' => 'checkbox',
						'#title' => $value2['label'],
						'#default_value' => $defaultValue,
					);
				}
			}
		}

		// Page title field.
		$form['path'] = array(
			'#type' => 'textfield',
			'#title' => $this->t('Location path:'),
			'#default_value' => $config->get('node_to_json.path'),
			'#description' => $this->t('there is a problem it is necessary to review the permits'),
			'#required' => TRUE,
		);
		$form['#attached']['library'][] = 'node_to_json/node_to_json';

		// to the form. This is not required, but is convention.
		$form['actions'] = [
			'#type' => 'actions',
		];

		// Add a submit button that handles the submission of the form.
		$form['actions']['submit'] = [
			'#type' => 'submit',
			'#value' => $this->t('Submit'),
		];

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
			//inform the problem whit permissions
			$form_state->setErrorByName('path', $this->t('there is a problem it is necessary to review the permits'));
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
		$configData = [];
		//get values of form
		$valuesForm = $form_state->getValues();

		foreach ($valuesForm as $key => $value) {
			//search content type checkbox and files
			if (strpos($key, '---')) {
				//search namfe file for content
				dpm($key);
				dpm($value);
				$fieldName = explode("---", $key);
				//determinate what to do based in array length
				switch (count($fieldName)) {
				case 2:
					//validate checkbox content type
					if (is_int($value) && $value == 1) {
						$configData[$fieldName[1]]['fields'] = [];
					}
					break;
				case 3:
					//validate checkbox for field
					if (is_int($value) && $value == 1) {
						dpm('campo check valido');
						if (array_key_exists($fieldName[1], $configData)) {
							dpm('campo check valido con existencia en array anterior');
							$configData[$fieldName[1]]['fields'][] = $fieldName[2];
						}
					}
					//validate select for filename

					dpm(is_string($value));
					dpm(strpos($value, 'filename'));
					if (array_key_exists($fieldName[1], $configData) && is_string($value) && strpos($key, 'filename')) {
						dpm('----');
						switch ($value) {
						case 'nid':
							$configData[$fieldName[1]]['file_name'] = 'nid';
							break;

						case 'title':
							$configData[$fieldName[1]]['file_name'] = 'title';
							break;
						}
					}

					break;
				}
				/*if (count($fieldName) == 1) {
						//get content type

					} elseif (count($fieldName) == 2 && array_key_exists($fieldName[0], $configData)) {
						//get field of content type
						$configData[$fieldName[0]]['fields'][] = $fieldName[1];
				*/
			}
		}
		dpm($configData);
		//get and set config module
		$config = \Drupal::service('config.factory')->getEditable('node_to_json.settings');
		$config->set('node_to_json.path', $form_state->getValue('path'));
		$config->set('node_to_json.content', $configData);
		$config->save();
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

	public function contentTypeFields() {
		$data = [];
		//get list of content types
		$list = node_type_get_types();
		$entity_type_id = 'node';
		foreach ($list as $key => $value) {
			$data[$key] = [];
			$data[$key]['label'] = $value->label();
			$data[$key]['fields'] = [];
			//get fields of content type
			foreach (\Drupal::entityManager()->getFieldDefinitions($entity_type_id, $key) as $field_name => $field_definition) {
				if (!empty($field_definition->getTargetBundle())) {
					$data[$key]['fields'][$field_name]['type'] = $field_definition->getType();
					$data[$key]['fields'][$field_name]['label'] = $field_definition->getLabel();
				}
			}
		}
		return $data;
	}
}
?>
