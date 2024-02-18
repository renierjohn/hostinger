<?php

namespace Drupal\Tests\aggregator\Functional\Update;

use Drupal\aggregator\Entity\Item;
use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests updates to Item entities.
 *
 * @group Update
 * @group aggregator
 */
class AggregatorUpdateItemsTest extends UpdatePathTestBase {

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
   * @covers aggregator_update_8604()
   */
  public function testUpdateHookN(): void {
    $this->runUpdates();

    $item = Item::load(1);
    if ($item instanceof Item) {
      $this->assertNotNull($item->uuid());
    }
    else {
      $this->fail('Item with ID 1 does not exist.');
    }
  }

}
