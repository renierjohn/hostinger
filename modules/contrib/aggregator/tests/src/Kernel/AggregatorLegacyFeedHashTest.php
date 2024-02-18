<?php

namespace Drupal\Tests\aggregator\Kernel;

use Drupal\aggregator\Entity\Feed;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

/**
 * Tests the deprecations of Feed hash functions.
 *
 * @group legacy
 * @group aggregator
 */
class AggregatorLegacyFeedHashTest extends EntityKernelTestBase {

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
  public function testDeprecationFeedHashFunctions() {
    $feed = Feed::create([
      'title' => 'Feed Hash Deprecation Test',
      'url' => 'https://example.com',
    ]);
    $feed->save();
    $this->expectDeprecation('Feed::setHash() is deprecated in aggregator:2.1.0 and is removed from aggregator:3.0.0. Use \Drupal::service("aggregator.items.importer")->setHash($feed, $hash); instead. See https://www.drupal.org/node/3386907.');
    $feed->setHash('abcde');
    $this->expectDeprecation('Feed::getHash() is deprecated in aggregator:2.1.0 and is removed from aggregator:3.0.0. Use \Drupal::service("aggregator.items.importer")->getHash($feed); instead. See https://www.drupal.org/node/3386907.');
    $hash = $feed->getHash();
    $this->assertSame('abcde', $hash);
  }

}
