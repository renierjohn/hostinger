<?php

namespace Drupal\csv_importer\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Importer manager interface.
 */
interface ImporterInterface extends PluginInspectionInterface, ContainerFactoryPluginInterface {

  public function importByBatchFile($filename,$header,$start_offset,$end_offset,$entity_type,$entity_type_bundle,$logger_csv_filename,$logger_csv_keys,&$context);

  // public function importByBatch($fields,$value,$entity_type,$entity_type_bundle,$logger_csv_filename,$logger_csv_keys,&$context);
  /**
   * Batch finish handler.
   *
   * @param bool $success
   *   A boolean indicating whether the batch has completed successfully.
   * @param array $results
   *   The value set in $context['results'] by callback_batch_operation().
   * @param array $operations
   *   Contains the operations that remained unprocessed.
   *
   * @return array
   *   Prepared data.
   */
  public function finished($success, $results, array $operations);

  /**
   * Run batch operations.
   */
  public function process();

}
