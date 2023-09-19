<?php

namespace Drupal\Tests\aggregator\Kernel\Migrate\d6;

use Drupal\Tests\SchemaCheckTestTrait;

/**
 * Upgrade variables to filter.format.aggregator_html.yml.
 *
 * @group aggregator
 */
class MigrateAggregatorFilterFormatTest extends MigrateDrupal6TestBase {

  use SchemaCheckTestTrait;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->executeMigration('d6_aggregator_filter_format');
  }

  /**
   * Tests migration of aggregator variables to aggregator.settings.yml.
   */
  public function testAggregatorSettings() {
    $config = $this->config('filter.format.aggregator_html');
    $this->assertSame('<a> <b> <br /> <dd> <dl> <dt> <em> <i> <li> <ol> <p> <strong> <u> <ul>', $config->get('filters.filter_html.settings.allowed_html'));
  }

}
