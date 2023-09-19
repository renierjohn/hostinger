<?php

namespace Drupal\Tests\aggregator\Kernel\Migrate\d7;

/**
 * Upgrade variables to filter.format.aggregator_html.yml.
 *
 * @group aggregator
 */
class MigrateAggregatorFilterFormatTest extends MigrateDrupal7TestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig(static::$modules);
    $this->executeMigration('d7_aggregator_filter_format');
  }

  /**
   * Tests migration of Aggregator variables to configuration.
   */
  public function testMigration() {
    $config = $this->config('filter.format.aggregator_html');
    $this->assertSame('<p> <div> <a>', $config->get('filters.filter_html.settings.allowed_html'));
  }

}
