<?php

namespace Drupal\Tests\aggregator\Functional\Update;

use Drupal\aggregator\Entity\Feed;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\UpdatePathTestTrait;

/**
 * @covers aggregator_post_update_delete_queue_items()
 * @group Update
 * @group aggregator
 */
class AggregatorUpdateDeleteQueueTest extends BrowserTestBase {

  use UpdatePathTestTrait;

  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = [
    'aggregator',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Since Aggregator was just installed Drupal thinks that it doesn't need to
    // run the update. Remove it from the update list so it can be run by the
    // test.
    $key_value = \Drupal::service('keyvalue');
    $existing_updates = $key_value->get('post_update')->get('existing_updates', []);
    if ($key = array_search('aggregator_post_update_delete_queue_items', $existing_updates)) {
      unset($existing_updates[$key]);
    }
    $key_value->get('post_update')->set('existing_updates', $existing_updates);
  }

  /**
   * Ensure all items in the aggregator_feeds queue are deleted.
   */
  public function testUpdateDeleteQueuePostUpdate(): void {
    $queue = \Drupal::queue('aggregator_feeds');
    // Any sample data would work. It isn't necessary to create a Feed entity
    // here to mimic the old queuing behavior. But we do it anyway.
    $feed = Feed::create([
      'title' => 'test feed',
      'url' => 'https://example.com/rss.xml',
      'refresh' => '900',
    ]);
    $queue->createItem($feed);
    $this->assertEquals(1, $queue->numberOfItems());

    $this->runUpdates();

    $this->assertEquals(0, $queue->numberOfItems());
  }

}
