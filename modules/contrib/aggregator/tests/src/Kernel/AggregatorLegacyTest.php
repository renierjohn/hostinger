<?php

namespace Drupal\Tests\aggregator\Kernel;

use Drupal\aggregator\Entity\Feed;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

/**
 * Tests the deprecations of Aggregator.
 *
 * @group legacy
 * @group aggregator
 */
class AggregatorLegacyTest extends EntityKernelTestBase {

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
    $this->installEntitySchema('aggregator_feed');
  }

  /**
   * @covers \Drupal\aggregator\Entity\Feed::__get
   */
  public function testDeprecationFeedProperties() {
    $feed = Feed::create([]);
    $this->expectDeprecation('The $items property is deprecated in 2.1.0 and will be removed from 3.0.0. See https://www.drupal.org/node/3386012.');
    $feed->items = [];
    $this->assertSame([], $feed->items);
    $this->expectDeprecation('The $source_string property is deprecated in 2.1.0 and will be removed from 3.0.0. See https://www.drupal.org/node/3386012.');
    $feed->source_string = 'abcde';
    $this->assertSame('abcde', $feed->source_string);
  }

}
