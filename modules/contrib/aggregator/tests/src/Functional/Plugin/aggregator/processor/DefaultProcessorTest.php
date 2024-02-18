<?php

namespace Drupal\Tests\aggregator\Functional\Plugin\aggregator\processor;

use Drupal\aggregator\ItemsImporter;
use Drupal\Tests\aggregator\Functional\AggregatorTestBase;

/**
 * @coversDefaultClass \Drupal\aggregator\Plugin\aggregator\processor\DefaultProcessor
 * @group aggregator
 */
class DefaultProcessorTest extends AggregatorTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * @covers ::submitConfigurationForm()
   * @covers ::deleteFeedHashes()
   */
  public function testSettingsForm() {
    $feed = $this->createFeed();
    $this->updateFeedItems($feed);

    /** @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface $key_value */
    $key_value = \Drupal::service('keyvalue.aggregator')->get($feed->id());

    // Verify that a hash key has been set in the key value store.
    $this->assertNotNull($key_value->get(ItemsImporter::AGGREGATOR_HASH_KEY));

    // Set a new value for the items.expire setting so that the hashes will be
    // deleted.
    $edit = [
      'aggregator_clear' => 3600,
    ];
    $this->drupalGet('admin/config/services/aggregator/settings');
    $this->submitForm($edit, 'Save configuration');

    // Verify that the hash key has been deleted.
    $this->assertNull($key_value->get(ItemsImporter::AGGREGATOR_HASH_KEY));
  }

}
