<?php

namespace Drupal\Tests\aggregator\Kernel\Plugin\Block;

use Drupal\aggregator\Entity\Feed;
use Drupal\aggregator\Entity\Item;
use Drupal\KernelTests\KernelTestBase;

/**
 * Class AggregatorFeedBlockTest.
 *
 * Test that we can render the block properly.
 *
 * @group aggregator
 */
class AggregatorFeedBlockTest extends KernelTestBase {

  public static $modules = [
    'aggregator',
    'block',
    'options',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('aggregator_feed');
    $this->installEntitySchema('aggregator_item');
  }

  public function testRenderBadItemUrl() {
    $aggregator_feed = Feed::create([
      'title' => 'testing title',
      'url' => 'http://www.example.com',
    ]);
    $aggregator_feed->save();

    // Create an aggregator feed item. It has invalid URI scheme.
    Item::create([
      'title' => 'Never Again',
      'fid' => $aggregator_feed->id(),
      'link' => 'xfiles s04e13',
    ]);
    // And another one, this time valid.
    $aggregator_item = Item::create([
      'title' => 'Never Again',
      'fid' => $aggregator_feed->id(),
      'link' => 'https://en.wikipedia.org/wiki/Never_Again_(The_X-Files)',
    ]);
    $aggregator_item->save();

    // Now try to render the block.
    /** @var \Drupal\Core\Block\BlockManagerInterface $block_manager */
    $block_manager = $this->container->get('plugin.manager.block');
    $block = $block_manager->createInstance('aggregator_feed_block', [
      'feed' => $aggregator_feed->id(),
    ]);

    // Now when trying to build it, there should be only the item with the
    // valid URL.
    $build = $block->build();
    $this->assertCount(1, $build["list"]["#items"]);
  }

}
