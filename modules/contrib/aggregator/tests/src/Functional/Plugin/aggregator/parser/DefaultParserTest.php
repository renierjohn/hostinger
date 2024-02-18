<?php

namespace Drupal\Tests\aggregator\Functional\Plugin\aggregator\parser;

use Drupal\Tests\aggregator\Functional\AggregatorTestBase;

/**
 * @coversDefaultClass \Drupal\aggregator\Plugin\aggregator\parser\DefaultParser
 * @group aggregator
 */
class DefaultParserTest extends AggregatorTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests that the parser plugin's settings subform works correctly.
   */
  public function testSettingsForm(): void {
    $this->drupalGet('admin/config/services/aggregator/settings');
    $edit = [
      'normalize_post_dates' => TRUE,
      'aggregator_parser' => 'aggregator',
    ];
    $this->submitForm($edit, 'Save configuration');
    $this->assertSession()->pageTextContains('The configuration options have been saved.');

    // Check that settings have the correct default value.
    foreach ($edit as $name => $value) {
      $this->assertSession()->fieldValueEquals($name, $value);
    }

    $edit = [
      'normalize_post_dates' => FALSE,
    ];
    $this->submitForm($edit, 'Save configuration');

    // Check that settings have the correct default value.
    foreach ($edit as $name => $value) {
      $this->assertSession()->fieldValueEquals($name, $value);
    }
  }

}
