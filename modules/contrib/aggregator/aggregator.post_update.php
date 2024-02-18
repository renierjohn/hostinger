<?php

/**
 * @file
 * Post update functions for Aggregator.
 */

use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\PluralTranslatableMarkup;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Delete the aggregator_feeds queue to eliminate old items containing entities.
 */
function aggregator_post_update_delete_queue_items(&$sandbox = NULL) {
  \Drupal::queue('aggregator_feeds')->deleteQueue();
}

/**
 * Add UUIDs to Aggregator Items.
 */
function aggregator_post_update_add_item_uuids(&$sandbox = NULL) {
  $entityStorage = \Drupal::entityTypeManager()->getStorage('aggregator_item');
  if (!isset($sandbox['ids'])) {
    // This must be the first run. Initialize the sandbox.
    $sandbox['ids'] = $entityStorage->getQuery()->accessCheck(FALSE)->execute();
    $sandbox['max'] = count($sandbox['ids']);
  }

  $uuid_service = \Drupal::service('uuid');
  $ids = array_splice($sandbox['ids'], 0, (int) Settings::get('entity_update_batch_size', 50));

  foreach ($entityStorage->loadMultiple($ids) as $item) {
    $item->set('uuid', $uuid_service->generate());
    $item->save();
  }

  $sandbox['#finished'] = empty($sandbox['max']) || empty($sandbox['ids']) ? 1 : ($sandbox['max'] - count($sandbox['ids'])) / $sandbox['max'];
  if ($sandbox['#finished'] === 1) {
    return new TranslatableMarkup('Finished updating aggregator items.');
  }
  return new PluralTranslatableMarkup($sandbox['max'] - count($sandbox['ids']),
    'Processed @count item of @total.',
    'Processed @count items of @total.',
    ['@total' => $sandbox['max']],
  );
}
