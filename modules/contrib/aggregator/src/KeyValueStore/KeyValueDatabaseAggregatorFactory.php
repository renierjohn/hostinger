<?php

namespace Drupal\aggregator\KeyValueStore;

use Drupal\Core\KeyValueStore\DatabaseStorage;
use Drupal\Core\KeyValueStore\KeyValueDatabaseFactory;

/**
 * Defines the key/value store factory for the database backend.
 */
class KeyValueDatabaseAggregatorFactory extends KeyValueDatabaseFactory {

  /**
   * {@inheritdoc}
   */
  public function get($collection) {
    return new DatabaseStorage($collection, $this->serializer, $this->connection, 'key_value_aggregator');
  }

}
