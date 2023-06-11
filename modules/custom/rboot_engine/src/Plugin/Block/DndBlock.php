<?php

namespace Drupal\rboot_engine\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "rboot_engine_dnd",
 *   admin_label = @Translation("Rboot DND"),
 *   category = @Translation("rboot_engine")
 * )
 */
class DndBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['content'] = [
      '#markup' => $this->t('It works!'),
    ];
    return $build;
  }

}
