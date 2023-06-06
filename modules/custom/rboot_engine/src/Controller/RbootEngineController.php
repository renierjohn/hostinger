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
  public function build() {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
