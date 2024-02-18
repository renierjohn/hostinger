<?php

namespace Drupal\aggregator\KeyValueStore;

use Drupal\Core\KeyValueStore\KeyValueFactory;

/**
 * Defines the key/value store factory.
 */
class KeyValueAggregatorFactory extends KeyValueFactory {

  const DEFAULT_SERVICE = 'keyvalue.aggregator.database';

  const SPECIFIC_PREFIX = 'keyvalue_aggregator_service_';

}
