<?php

namespace Drupal\Tests\aggregator\Kernel;

use Drupal\aggregator\Entity\Item;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

/**
 * Tests the deprecations of Aggregator.
 *
 * @group legacy
 * @group aggregator
 */
class AggregatorLegacyItemTest extends EntityKernelTestBase{

  use ExpectDeprecationTrait;

  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = ['aggregator', 'options'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('aggregator_item');
  }

  /**
   * @covers \Drupal\aggregator\Entity\Item::buildUri
   */
  public function testDeprecationItemBuildUri() {
    $item = Item::create([
      'link' => 'https://example.com/feed.xml',
    ]);
    $this->expectDeprecation('Item::buildUri() is deprecated in aggregator:2.2.0 and will be removed from aggregator:3.0.0. Use Item::buildItemUri() instead.');
    $url = Item::buildUri($item);
    $this->assertSame('https://example.com/feed.xml', $url->toString());
  }

}
