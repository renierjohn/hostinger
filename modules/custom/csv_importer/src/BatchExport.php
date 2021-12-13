<?php

namespace Drupal\csv_importer;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Drupal\Core\File\FileSystemInterface;
/**
 * Provides a base class for ImporterBase plugins.
 *
 * @see \Drupal\csv_importer\Annotation\Importer
 * @see \Drupal\csv_importer\Plugin\ImporterManager
 * @see \Drupal\csv_importer\Plugin\ImporterInterface
 * @see plugin_api
 */

class BatchExport {

    protected $limit;

    protected $content_type;

    protected $entity_type;

    protected $langcode;

    protected $translatable;

    protected $mids;

    protected $status;
    /**
     * {@inheritdoc}
     */
    public function __construct($content_type,$entity_type,$langcode,$translatable){
        $this->limit        = 100;
        $this->content_type = $content_type;
        $this->entity_type  = $entity_type;
        $this->langcode     = $langcode;
        $this->translatable = $translatable;
    }

    public function setMiddleCategory($mids){
      $this->mids = $mids;
    }

    public function setStatus($status){
      $this->status = $status;
    }

    public function process($nids,$filename,$langcode,$entity_type,$translatable,&$context) {
        $limit       = 100;
        $SEPARATOR   = ';';
        $entity      = \Drupal::service('csv_importer.entity');
        $contents    = $entity->ids($nids)->setTranslatable($translatable)->entityType($entity_type)->langcode($langcode)->loadAll();
        $total_row   = count($contents);
        $context['results']['langcode'] = $langcode;

        if (empty($context['sandbox'])) {
          $context['sandbox'] = [];
          $context['sandbox']['progress'] = 1;
          $context['sandbox']['current_node'] = 0;
          $context['sandbox']['max'] = $total_row;
          $context['finished'] = FALSE;
          $context['results']['filename'] = $filename;

          /** if header flag is true , append the header , else ignore the header*/
          $csv_file     = fopen($filename, 'a');
          $header = \Drupal::service('state')->get('csv_file_header_state');
          if($header == 1){
            $temp_header = [];
            fputcsv($csv_file,array_keys($contents[0]),$SEPARATOR);
            $header = \Drupal::service('state')->set('csv_file_header_state',0);
          }
        }

        if($limit > $total_row){
            $limit = $total_row ;
        }

        $min = $context['sandbox']['current_node'];
        $max = $context['sandbox']['current_node'] + $limit - 1;

        if( $max >= $total_row) {
          $max = $total_row - 1;
        }
        $result = range( $min , $max );
        foreach ($contents as $key => $content) {
          fputcsv($csv_file,$content,$SEPARATOR);
          $row  = $key;
          $context['results'][] = 'row # '.($row);
          $context['sandbox']['progress']++;
          $context['sandbox']['current_node'] = $row;
          $context['message'] = t('Generating CSV File Contents . Row number @row / @total , limit : @limit', [
            '@row'    => $row,
            '@limit'  => $limit,
            '@total'  => $total_row,
          ]);
        }

        if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
          $context['finished'] = $context['sandbox']['progress'] > $context['sandbox']['max'];
          fclose($csv_file);
        }
    }

  function batch_finished($success, $results, $operations) {
    if(empty($results['filename'])){
      \Drupal::messenger()->addWarning('No Contents Available for '.$results['langcode']);  
    }
    else{
    $filename = $results['filename'];
    \Drupal::service('state')->set('csv_file_download',$filename);
    \Drupal::messenger()->addMessage(t('Success , Please Click Download Button to download @filename',['@filename' => str_replace('public://','',$filename)]));
    }
  }

  public function execute(){
    $entity       = \Drupal::service('csv_importer.entity');
    $langcode     = $this->langcode;
    $content_type = $this->content_type;
    $entity_type  = $this->entity_type;
    $translatable = $this->translatable;
    $status       = $this->status;
    // $mids         = $this->mids;
    $filename     = 'public://'.$content_type.'_'.$langcode.'.csv';

    $index        = 0;
    $limit        = $this->limit;
    $process      = [];
    $process['operations']['filename'] = $filename;
    /** ovverwrite the file and set initial state for adding header*/
    $csv_file     = fopen($filename, 'w+');
    fclose($csv_file);

    \Drupal::service('state')->set('csv_file_header_state',1);

    $ids = [];

    while(TRUE){
      if($entity_type == 'node'){
        $ids = $entity->loadAllProductsByBatch($index,$limit,$content_type,$langcode,$status);
      }
      if($entity_type == 'taxonomy_term'){
        $ids = $entity->loadAllCategoryByBatch($index,$limit,$content_type,$langcode,$status);
      }
      if(empty($ids)){
        break;
      }
      $process['operations'][] = [
        [$this,'process'],
        [$ids,$filename,$langcode,$entity_type,$translatable]
      ];
      $index += $limit;
    }    
    $process['finished'] = [$this, 'batch_finished'];
    batch_set($process);
  }
}
