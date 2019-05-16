<?php

namespace Drupal\node_to_json\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Defines NodeToJson class.
 */
class NodeToJsonController extends ControllerBase {

	/**
	 * Display the markup.
	 *
	 * @return array
	 *   Return markup array.
	 */
	public function content() {
		return [
			'#type' => 'markup',
			'#markup' => $this->t('Hello, etce World!'),
		];
	}

}
