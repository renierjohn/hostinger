<?php

namespace Drupal\Tests\aggregator\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * @covers aggregator_update_8606()
 * @group Update
 * @group aggregator
 */
class AggregatorUpdateBrokenRssFeedViewTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      DRUPAL_ROOT . '/core/modules/system/tests/fixtures/update/drupal-9.4.0.bare.standard.php.gz',
      __DIR__ . '/../../../fixtures/update/aggregator.php',
      __DIR__ . '/../../../fixtures/update/aggregator_2_1_0.php',
    ];
  }

  /**
   * Ensure views.view.aggregator_rss_feed is updated.
   */
  public function testUpdateHookN(): void {
    $old_view_config = \Drupal::config('views.view.aggregator_rss_feed');
    $this->assertSame([
      'row' => [
        'type' => 'aggregator_rss',
        'options' => [
          'relationship' => 'none',
          'view_mode' => 'summary',
        ],
      ],
    ], $old_view_config->get('display.feed_items.display_options'));

    $this->runUpdates();

    $new_view_config = \Drupal::config('views.view.aggregator_rss_feed');
    $this->assertSame([
      'row' => [
        'type' => 'aggregator_rss',
        'options' => [
          'relationship' => 'none',
          'view_mode' => 'summary',
        ],
      ],
      'defaults' => [
        'arguments' => TRUE,
      ],
      'display_description' => '',
      'display_extenders' => [],
      'path' => 'aggregator/rss',
    ], $new_view_config->get('display.feed_items.display_options'));
  }

}
