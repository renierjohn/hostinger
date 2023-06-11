<?php

namespace Drupal\rboot_engine\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for rboot_engine routes.
 */
class RbootEngineController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function request($type, $id = False) {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!' . $type . $id ),
    ];

    return $build;
  }

}
