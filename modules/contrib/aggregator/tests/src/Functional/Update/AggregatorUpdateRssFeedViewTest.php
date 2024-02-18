<?php

namespace Drupal\Tests\aggregator\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * @covers aggregator_update_8601()
 * @group Update
 * @group aggregator
 */
class AggregatorUpdateRssFeedViewTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      DRUPAL_ROOT . '/core/modules/system/tests/fixtures/update/drupal-9.4.0.bare.standard.php.gz',
      __DIR__ . '/../../../fixtures/update/aggregator.php',
    ];
  }

  /**
   * Ensure views.view.aggregator_rss_feed is updated.
   */
  public function testUpdateHookConfigMatch(): void {
    $this->runUpdates();

    $view_config = \Drupal::config('views.view.aggregator_rss_feed');
    $this->assertSame([
      'core.entity_view_mode.aggregator_item.summary',
    ], $view_config->get('dependencies.config'));
    $this->assertSame([], $view_config->get('display.default.display_options.fields'));
    $this->assertSame([
      'timestamp' => [
        'id' => 'timestamp',
        'table' => 'aggregator_item',
        'field' => 'timestamp',
        'relationship' => 'none',
        'group_type' => 'group',
        'admin_label' => '',
        'entity_type' => 'aggregator_item',
        'entity_field' => 'timestamp',
        'plugin_id' => 'date',
        'order' => 'DESC',
        'expose' => [
          'label' => '',
          'field_identifier' => '',
        ],
        'exposed' => FALSE,
        'granularity' => 'second',
      ],
    ], $view_config->get('display.default.display_options.sorts'));
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
    ], $view_config->get('display.feed_items.display_options'));
  }

  /**
   * Ensure a message is displayed informing that the view cannot be updated.
   */
  public function testUpdateHookConfigMismatch(): void {
    $view_config = \Drupal::configFactory()->getEditable('views.view.aggregator_rss_feed');
    // Change the view so that the config will not match the default hash.
    $view_config->set('status', FALSE);
    $view_config->save();

    $this->runUpdates();

    $this->assertSession()->responseContains("Unfortunately, this site's view has been modified from the original and
    cannot be updated automatically.");
  }

}
