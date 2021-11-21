<?php

namespace Drupal\data_router\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\HtmlResponse;

/**
 * Class ImceAdminBrowserController.
 */
class QRController extends ControllerBase {

  public function scan(){
  	$build = [
  		'#theme' => 'qr',
  		'#attached' => [
  			'library' => [
  				'data_router/qr'
  			]
  		],
  	];
  	return $build;
  }

}
