<?php

namespace Drupal\node_to_json\Controller;

//use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Defines NodeToJson class.
 */
class NodeToJsonController extends ControllerBase {

	public function getNodeRest(int $nid) {
		//get the module config
		$default_config = \Drupal::config('node_to_json.settings');
		$path = $default_config->get('node_to_json.path');
		$contentType = $default_config->get('node_to_json.content');

		// Initialize the response array.
		$response_array = [];
		$entity = Node::load($nid);

		//the type of entity is validated
		if (array_key_exists($entity->bundle(), $contentType)) {
			//get common fields
			//dpm(($entity->id()));

			$title = $entity->getTitle();
			$data['data']['title'] = $title;
			//$data['data']['body'] = $entity->get('body')->value;
			$data['data']['created'] = $entity->getCreatedTime();
			$data['data']['updated'] = $entity->getChangedTime();
			$data['data']['lastModifiedBy'] = $entity->getRevisionAuthor()->getUsername();

			$fileNameType = $contentType[$entity->bundle()]['file_name'];
			//get particular fields
			foreach ($contentType[$entity->bundle()]['fields'] as $keyField => $nameField) {
				//get field type
				$fieldType = $entity->get($nameField)->getFieldDefinition()->getType();
				switch ($fieldType) {
				case 'text_with_summary':
					$valueField = $entity->get($nameField)->getValue();
					$data['data'][$nameField] = $valueField[0]['value'];
					break;
				case 'string':
					$valueField = $entity->get($nameField)->getValue();
					$data['data'][$nameField] = $valueField[0]['value'];
					break;
				case 'image':
					//validate if file image exist
					if ($entity->hasField($nameField) && !empty($entity->get($nameField)->getValue())) {
						$imageData = $entity->get($nameField)->getValue();
						//validate image metada
						if (is_array($imageData) && array_key_exists('title', $imageData[0]) && !empty($imageData[0]['title'])) {
							$imageJson['title'] = $imageData[0]['title'];
						} else {
							$imageJson['title'] = $title;
						}
						if (is_array($imageData) && array_key_exists('alt', $imageData[0]) && !empty($imageData[0]['alt'])) {
							$imageJson['alt'] = $imageData[0]['alt'];
						} else {
							$imageJson['alt'] = $title;
						}
						//validate if data have fid
						if (is_array($imageData) && array_key_exists('target_id', $imageData[0])) {
							//get preset images
							$imageStyles = ImageStyle::loadMultiple();
							// get file data
							$imageJson['preset'] = [];
							$file = \Drupal\file\Entity\File::load($imageData[0]['target_id']);
							//go over preset image array
							foreach ($imageStyles as $key => $value) {
								$style = \Drupal::entityTypeManager()->getStorage('image_style')->load($value->getName());
								//get url image whit preset
								$url = $style->buildUrl($file->getFileUri());
								$imageJson['preset'][$value->getName()] = $url;
							}
							$imageJson['absolutePath'] = file_create_url($file->getFileUri());
						} else {}
					}
					//validate if image is not empty
					if (!empty($imageJson)) {
						$data['data']['image'] = [];
						$data['data']['image'] = $imageJson;
					}
					break;
				}
			}
			$response = new jsonResponse($data);
		} else {
			//close if content type
			$data['data']['message'] = "bad content";
			$response = new jsonResponse($data);
			$response->setStatusCode(404);
		}

		return $response;
	}

}
