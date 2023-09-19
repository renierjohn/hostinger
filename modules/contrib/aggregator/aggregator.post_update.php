<?php

/**
 * @file
 * Post update functions for Aggregator.
 */

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
  $uuid_service = \Drupal::service('uuid');

  foreach (\Drupal\aggregator\Entity\Item::loadMultiple() as $item) {
    $item->set('uuid', $uuid_service->generate());
    $item->save();
  }
}
