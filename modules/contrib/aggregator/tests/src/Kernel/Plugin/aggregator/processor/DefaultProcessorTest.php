<?php

namespace Drupal\Tests\aggregator\Kernel\Plugin\aggregator\processor;

use Drupal\aggregator\Entity\Feed;
use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\aggregator\Plugin\aggregator\processor\DefaultProcessor
 * @group aggregator
 */
class DefaultProcessorTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['aggregator', 'options'];

  /**
   * An instance of the DefaultProcessor plugin.
   *
   * @var \Drupal\aggregator\Plugin\aggregator\processor\DefaultProcessor
   */
  protected $processor;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('aggregator_feed');
    $this->installEntitySchema('aggregator_item');

    /** @var \Drupal\aggregator\Plugin\AggregatorPluginManager $plugin_manager */
    $plugin_manager = $this->container->get('plugin.manager.aggregator.processor');
    /** @var \Drupal\aggregator\Plugin\aggregator\processor\DefaultProcessor $processor */
    $this->processor = $plugin_manager->createInstance('aggregator');
  }

  /**
   * @covers ::process()
   *
   * @dataProvider provideProcessData
   */
  public function testProcess(array $items, array $expected_item_guids) {
    $feed = Feed::create([
      'title' => 'Processor test feed',
      'url' => 'https://example.com/rss.xml',
      'items' => $items,
    ]);
    $feed->save();

    $this->processor->process($feed);

    // Assert that the items we expected were created.
    $guid_check_results = \Drupal::entityQuery('aggregator_item')
      ->accessCheck(FALSE)
      ->condition('guid', $expected_item_guids)
      ->execute();
    $this->assertSame(count($expected_item_guids), count($guid_check_results));
    // Assert that no additional items were created.
    $total_items_results = \Drupal::entityQuery('aggregator_item')
      ->accessCheck(FALSE)
      ->execute();
    $this->assertSame(count($expected_item_guids), count($total_items_results));
  }

  /**
   * The data provider for testProcess().
   *
   * @return array
   */
  public function provideProcessData(): array {
    return [
      'bad_timestamp' => [
        [
          [
            'link' => 'https://example.com',
            'title' => 'Feed item with bad timestamp',
            'description' => 'This item has a very old timestamp, which is known to have caused a PDOException that breaks the entire import process.  See https://www.drupal.org/project/aggregator/issues/1695852.',
            'author' => '',
            'guid' => 1,
            'timestamp' => -2209172400,
          ],
          [
            'link' => 'https://example.com',
            'title' => 'Good feed item',
            'description' => 'This is a good feed item and should be imported properly even after the bad item fails.',
            'author' => '',
            'guid' => 2,
            'timestamp' => 0,
          ],
        ],
        [2],
      ],
    ];
  }

}
