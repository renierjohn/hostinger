<?php

namespace Drupal\Tests\aggregator\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests updates to Feed entities.
 *
 * @group Update
 * @group aggregator
 */
class AggregatorUpdateFeedsTest extends UpdatePathTestBase {

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
   * @covers aggregator_update_8605()
   */
  public function testUpdateHookN(): void {
    $this->runUpdates();
    $update_manager = \Drupal::entityDefinitionUpdateManager();
    $this->assertNull($update_manager->getFieldStorageDefinition('hash', 'aggregator_feed'));

  }

}
