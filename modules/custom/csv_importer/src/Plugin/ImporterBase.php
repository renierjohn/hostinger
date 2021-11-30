<?php

namespace Drupal\csv_importer\Plugin;

use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\File\FileSystemInterface;

use Drupal\pathauto\PathautoGenerator;

use Drupal\Component\Utility\Unicode;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\csv_importer\Service\LoggerService;

/**
 * Provides a base class for ImporterBase plugins.
 *
 * @see \Drupal\csv_importer\Annotation\Importer
 * @see \Drupal\csv_importer\Plugin\ImporterManager
 * @see \Drupal\csv_importer\Plugin\ImporterInterface
 * @see plugin_api
 */
abstract class ImporterBase extends PluginBase implements ImporterInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The config service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;/**/
  /**
   * The logger service.
   *
   * @var \Drupal\csv_importer\Service\LoggerService
   */
  protected $logger;

  protected $csv;

  /**
   * Constructs ImporterBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config,LoggerService $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->config = $config;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('csv_importer.logger')
    );
  }
  /**
   * {@inheritdoc}
   */
  public function process() {
    $process            = [];
    $logger             = $this->logger;
    // $csv                = $this->configuration['csv'];
    $csv_entity         = $this->configuration['csv_entity'];
    $entity_type        = $this->configuration['entity_type'];
    $entity_type_bundle = $this->configuration['entity_type_bundle'];
    $entity_definition  = $this->entityTypeManager->getDefinition($entity_type);
    
    /** Logger*/ 
      $logger_start        = $logger->setBundle($entity_type_bundle)->setEntityType($entity_type)->startLog(); // logs for missing files
      $logger_csv_filename = $logger_start['filename'];
      $logger_csv_keys     = $logger_start['keys'];
    /** End Logger*/ 

     $filename       = $csv_entity->get('uri')->getValue()[0]['value'];
     $total_items    = $this->getTotalRow($filename)['count'];
     $fields         = $this->getTotalRow($filename)['header'];
     $header = [];

     foreach ($fields as $field) {
      $header[] = explode('|',$field)[0]; // normalized the header
     }

    $this->logs('Start Import , content-type:'.$entity_type_bundle.' filepath:'.$filename.' : Total Items:'.$total_items);
    
     /**
      * START VALIDATING CSV
      * */ 
     $id_flag = FALSE;
     if(in_array('tid',$header) || in_array('nid',$header)){
      $id_flag = TRUE;
     }

     if($id_flag == FALSE){
      if($entity_type == 'node'){
        $this->messenger()->addError(t('nid column is missing'));
        $this->logs('nid column is missing'.' filepath:'.$filename);
      }

      if($entity_type == 'taxonomy_term'){
       $this->messenger()->addError(t('tid column is missing')); 
       $this->logs('tid column is missing'.' filepath:'.$filename);
      }
      return;
     }

    if(!in_array('langcode',$header)){
         $this->messenger()->addError(t('langcode column is missing')); 
         $this->logs('langcode column is missing'.' filepath:'.$filename);
         return;
    }

    // ADDING MORE VALIDATION
    if(!in_array('status',$header)){
         $this->messenger()->addError(t('status column is missing')); 
         $this->logs('status column is missing'.' filepath:'.$filename);
         return;
    }

    if($entity_type == 'node'){
      if(in_array('tid',$header) || in_array('name',$header)){
        $this->messenger()->addError(t('Found Mismatch Entity Type. Please Select Taxonomy term')); 
        $this->logs('Mismatch Entity Type'.' filepath:'.$filename);
        return; 
      }
      if(!in_array('title',$header)){
        $this->messenger()->addError(t('title column is missing')); 
        $this->logs('title column is missing'.' filepath:'.$filename);
        return;
      }
    }

    if($entity_type == 'taxonomy_term'){
      if(in_array('nid',$header) || in_array('title',$header)){
        $this->messenger()->addError(t('Found Mismatch Entity Type. Please select content')); 
        $this->logs('Mismatch Entity Type'.' filepath:'.$filename);
        return; 
      }
       if(!in_array('name',$header)){
        $this->messenger()->addError(t('name column is missing')); 
        $this->logs('name column is missing'.' filepath:'.$filename);
        return;
      }
    }

    $field_definitions = \Drupal::service('entity_field.manager')->getFieldDefinitions($entity_type,$entity_type_bundle);    
    \Drupal::service('state')->set('csv_translable_flag',FALSE);
    foreach ($header as $field) {
      if($field == 'tid' || $field == 'nid' || $field == 'title' || $field == 'name' || $field == 'langcode' || $field == 'status' || $field == 'delete' ){
        continue;
      }
      if(empty($field_definitions[$field])){        
        $this->messenger()->addError(t('Found Mismatch Entity Bundle . Please check the fields'));
        $this->logs('Some Fields are not match'.' filepath:'.$filename); 
        \Drupal::service('state')->set('csv_translable_flag',FALSE);
        return;
      }
      if(!$field_definitions[$field]->isTranslatable()){
        \Drupal::service('state')->set('csv_translable_flag',TRUE);
      }    
    }
    // END
    


     $items_per_page = 500;

     if($total_items > 500 && $total_items < 1000 ){
       $items_per_page = 100;      
     }

     if($total_items > 100 && $total_items < 500){
       $items_per_page = 50;      
     }

     if($total_items > 10 && $total_items < 100){
       $items_per_page = 10;      
     }

     if($total_items > 0 && $total_items < 10){
       $items_per_page = 1;      
     }

     $pages          = $total_items / $items_per_page;
     $pages          = floor($pages);
     $iterators      = range(0,$pages);     

     foreach($iterators as $iterator){
          $start_offset = $iterator * $items_per_page + 1;
          $end_offset   = ($iterator + 1) * $items_per_page;

          if($total_items < $end_offset ){
              $end_offset = $total_items;
          }

          $process['operations'][] = [
              [$this, 'importByBatchFile'],[$filename,$header,$start_offset,$end_offset,$entity_type,$entity_type_bundle,$logger_csv_filename,$logger_csv_keys]
          ];               
     }
     $process['finished'] = [$this, 'finished'];
     batch_set($process);     
  }

  public function getTotalRow(&$filename){
    $csv = fopen($filename,'r');
    $row = 0;
    $header = fgetcsv($csv,10000);
    while(fgetcsv($csv,10000)) {
      $row++;
    }
    fclose($csv);
    return ['header'=> $header , 'count' => $row];
  }

  /**
   * @param $filename            - json filename
   * @param header               - header of csv or mahine names
   * @param $start_offset        - starting point of csv
   * @param $end_offset          - ending point of csv
   * @param $entity_type         - entity_type | node or taxonomy_term
   * @param $entity_type_bundle  - content type or vocabulary
   * @param $logger_csv_filename - filename for missing files in csv format
   * @param $logger_csv_keys     - list of machine_names that are file and entity_reference datatype
   * */ 
  public function importByBatchFile($filename,$header,$start_offset,$end_offset,$entity_type,$entity_type_bundle,$logger_csv_filename,$logger_csv_keys,&$context) {
    $field_definitions = \Drupal::service('entity_field.manager')->getFieldDefinitions($entity_type,$entity_type_bundle);
    $default_langcode  = \Drupal::languageManager()->getDefaultLanguage()->getId();
    $entity            = \Drupal::service('entity_type.manager');
    $pathauto          = \Drupal::service('pathauto.generator');
    $logger            = \Drupal::service('csv_importer.logger');
    $ram_usage         =  round(memory_get_usage(true)/1048576).'MB'; // convert from Bytes into MB
    $fields_arrs       = [];
    $datas             = [];
    
    $csv_file_handler = fopen($filename,'r');
    $row = 0;
    while ($data = fgetcsv($csv_file_handler)) {
      if($row >= $start_offset && $row <= $end_offset ){
        $fields_arrs[] = array_combine($header,$data);   
      }
      if($row > $end_offset){
        break;
      }
      $row++;
    }
    fclose($csv_file_handler);


    $total_array = count($fields_arrs);
    if (empty($context['sandbox'])) {
      $context['sandbox'] = [];
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_node'] = 0;
      $context['sandbox']['max'] = $total_array - 1;
      $context['finished'] = FALSE;
    }

      foreach ($fields_arrs as $key => $fields_arr) {

            if(!in_array($fields_arr['langcode'],['en','ja','ko','de','zh-hans'])){ // check first if langcode is valid
                if($entity_type == 'node'){
                  $missing_lang_id = $fields_arr['nid'];
                }
                if($entity_type == 'taxonomy_term'){
                  $missing_lang_id = $fields_arr['tid'];
                }        
                $csv_import_log_file = fopen('private://csv_import.txt','a');
                $date = date("F d,Y h:i:s A");
                fwrite($csv_import_log_file,$date.' ID:'.$missing_lang_id.' invalid langcode:'.$fields_arr['langcode'].' '.$entity_type_bundle.PHP_EOL);
                fclose($csv_import_log_file);

                $context['sandbox']['progress']++ ;
                $context['sandbox']['current_node']++;
                $context['message'] = t('Invalid langcode @langcode', [
                  '@langcode' => $fields_arr['langcode'],
                ]); 
               continue;
            }

            $row             = $key;         
            $fields_temp_arr = [];
            
            /**
             * Logger
             * */ 
            $tmpfile     = tmpfile();
            $tmpfile_uri = stream_get_meta_data($tmpfile)['uri'];
            $log         = $logger->setTmpUri($tmpfile_uri);
            /****************/

            /** Condition must have header nid or tid and delete to perform delete operation */ 
            /** DELETE CONTENT */ 
            if(!empty($fields_arr['delete']) && (!empty($fields_arr['nid']) || !empty($fields_arr['tid']))){
              $entity_content = [];
              $langcode       = isset($fields_arr['langcode']) ? $fields_arr['langcode'] : $default_langcode;

              if($fields_arr['delete'] == 1){
                $id = !empty($fields_arr['nid']) ? $fields_arr['nid'] : $fields_arr['tid'];
                $entity_content = $entity->getStorage($entity_type)->load($id);
              }
              
              if(!empty($entity_content)){
                 \Drupal::service('headless.file')->registerDeletedItems($entity_content); // register the deleted items for realtime update
                 $entity_content->delete();
                 $date = date("F d,Y h:i:s A");
                 $file = fopen('private://deleted_content_logs.txt','a');
                 fwrite($file,$date.' : '.'DELETED ID:'.$id.' ,  content-type:'.$entity_type_bundle.PHP_EOL);
                 fclose($file);
              }

              /**
               * if delete flag is 1 and nid has data but not exists on database, it will create new content
               * Otherwice if delete flag is 1 but nid is not exists on database, it wont do nothing  
               * */ 
              if($fields_arr['delete'] == 1){ 
                $context['results']['count_delete'][] = 'row # '.strval($row);
                $context['sandbox']['progress']++ ;
                $context['sandbox']['current_node']++;
                $context['message'] = t('Delete @content_type, ID: @id', [
                  '@content_type' => $entity_type_bundle,
                  '@id' => $id
                ]); 
                $context['results']['langcode'][$langcode] = $langcode;
                continue;                
              }              
            }            
            unset($fields_arr['delete']);
            /** preprocessing fields */ 
            foreach ($fields_arr as $field_arr_key => $field_arr_value) {              
                  if(empty($field_definitions[$field_arr_key])){
                    // \Drupal::logger('CSV Import')->error('invalid key '.$field_arr_key);
                    $csv_import_log_file = fopen('private://csv_import.txt','a');
                    $date = date("F d,Y h:i:s A");
                    fwrite($csv_import_log_file,$date.' : invalid key '.$field_arr_key.' '.$entity_type_bundle.PHP_EOL);
                    fclose($csv_import_log_file);
                    continue;
                  }

                  $field_type     = $field_definitions[$field_arr_key]->getType();
                  $field_settings = $field_definitions[$field_arr_key]->getSettings();
                  $cardinality    = $field_definitions[$field_arr_key]->getFieldStorageDefinition()->getCardinality();
                
                  $normal_field   = TRUE; // detect if field is not entity referenced
                  
                  if($cardinality > 1){ // detect if the field is multiple values
                    $tmp_multiple_field = explode(',',$field_arr_value);
                  }

                  if($field_type == 'entity_reference_revisions'){ // skip if field type is paragraph
                    $normal_field = FALSE;
                    continue;
                  }

                  if( $field_type == 'entity_reference'){  
                    $normal_field = FALSE;
                    if(empty($field_arr_value) && $field_arr_value != '0'){
                      continue; // dont insert the empty value from entity_references
                    }
                  }

                  if($field_type == 'file' || $field_type == 'image'){
                      $normal_field = FALSE;
                      $file_status  = \Drupal::service('csv_importer.entity')->setUri($field_arr_value)->getFileAssets();
                      if(!empty($file_status['missing_uri'])){
                        /** LOGGER */
                        $missing_files = implode(',',$file_status['missing_uri']); 
                        $log->setField([$field_arr_key => $missing_files])->appendFieldLog(); // collect the missing file logs 
                        // continue; // dont insert the empty value from entity_references, url is on csv file but doesnt have physical file on imce                        
                        $field_arr_value = FALSE;
                      }
                      
                      if(!empty($file_status['fid'])){
                        $field_arr_value = $file_status['fid'];
                      }
                  }
                  
                  if( $field_type == 'entity_reference'){
                      $normal_field = FALSE;
                      $target_type      = $field_settings['target_type'];
                      $handlerSettings  = $field_settings['handler_settings']['target_bundles']; // array ['key'=>'value','key1'=>'value1']
                      $name_status      = \Drupal::service('csv_importer.entity')->setHandlerSettings($handlerSettings)->entityType($target_type)->setNames($field_arr_value)->getIDFromNames();
                      if(!empty($name_status['mismatch_name'])){
                        /** LOGGER */
                        $missing_files = implode(',',$name_status['mismatch_name']); 
                        $log->setField([$field_arr_key => $missing_files])->appendFieldLog(); // collect the missing entity_reference logs 
                        $field_arr_value = FALSE;
                        // continue; // dont insert the empty value from entity_references
                      }
                      
                      if(!empty($name_status['id'])){
                        $field_arr_value = $name_status['id'];
                      }

                      if(empty($field_arr_value)){
                        continue;
                      } 
                    // $field_arr_value = explode(',',$field_arr_value); // detect if multiple id 
                  }

                  if($normal_field == TRUE && !empty($tmp_multiple_field) && $cardinality > 1){ // store the normal values as an array
                    $field_arr_value = $tmp_multiple_field;
                  }

                  $fields_temp_arr[$field_arr_key] = $field_arr_value;
            }

            /** LOGGER */ 
            $logger->setTmpUri($tmpfile_uri)
                   ->setCsvUri($logger_csv_filename)
                   ->setContent($fields_arr)
                   ->setEntityType($entity_type)
                   ->setCsvKeys($logger_csv_keys)
                   ->appendRowLog(); // logs all the the missing files on fields in every node array or term array
            /** END LOGGER */ 
            
            $fields_arr = $fields_temp_arr;
            $langcode   = isset($fields_arr['langcode']) ? $fields_arr['langcode'] : $default_langcode;
            $id         = isset($fields_arr['nid'])      ? $fields_arr['nid']      : FALSE;
            $status     = isset($fields_arr['status'])   ? $fields_arr['status']   : 1;
            $moderation = $status == 1 ? 'published' : 'unpublish';
            unset($fields_arr['nid']);
            // unset($fields_arr['status']);
            // unset($fields_arr['langcode']);          

            if(empty($id)){
              $id = isset($fields_arr['tid']) ? $fields_arr['tid'] : FALSE;
              unset($fields_arr['tid']);
              unset($fields_arr['ja']);
            }

            if($entity_type == 'node'){
              $fields_arr['type'] = $entity_type_bundle;
            }

            if($entity_type == 'taxonomy_term'){
              $fields_arr['vid'] = $entity_type_bundle;
            }

            /** If ID is empty , ADD CONTENT */ 
            if(empty($id)){    
                   /** Detect if the content is already exist
                    *  Do nothing when already exists and ID is empty
                    * */ 
                   $ids_flag_result   = FALSE;
                   $title_flag_result = FALSE;
                   $ids_flag_query    = $entity->getStorage($entity_type)->getQuery();
                   // if($entity_type == 'node'){
                   //    $ids_flag_query->condition('type',$entity_type_bundle);
                   //    $ids_flag_query->condition('title',$fields_arr['title']);
                   //    $ids_flag_result   = $ids_flag_query->execute();
                   //    $title_flag_result = $fields_arr['title'];
                   //  }

                    if($entity_type == 'taxonomy_term'){
                      $ids_flag_query->condition('vid',$entity_type_bundle);
                      $ids_flag_query->condition('name',$fields_arr['name']);
                      $ids_flag_result = $ids_flag_query->execute();
                      $title_flag_result = $fields_arr['name'];
                    }
              if(empty($ids_flag_result)){
                $entity_content = $entity->getStorage($entity_type)->create($fields_arr);
                // $entity_content = $entity->getStorage($entity_type)->create($fields_arr)->set('moderation_state',$moderation);
                $entity_content->save();
                $pathauto->createEntityAlias($entity_content,'bulkupdate');
                $context['results']['count_add'][] = 'row # '.strval($row);
              }else{
                $csv_import_log_file = fopen('private://csv_import.txt','a');
                $date = date("F d,Y h:i:s A");
                fwrite($csv_import_log_file,$date.' : Found Duplicate Content '.$title_flag_result.' '.$entity_type_bundle.PHP_EOL);
                fclose($csv_import_log_file);
                $context['results']['count_duplicate'][] = 'row # '.strval($row);
              }
            }

            /** If ID is detected , UPDATE CONTENT */ 
            if(!empty($id)){
              $entity_content     = $entity->getStorage($entity_type)->load($id);
              /** DETECT IF CONTENT IS DELETED WHILE UPDATING*/ 
              if(empty($entity_content)){
                  $csv_import_log_file = fopen('private://csv_import.txt','a');
                  $date = date("F d,Y h:i:s A");
                  fwrite($csv_import_log_file,$date.' ID:'.$id.' not exists while updating, '.$entity_type_bundle.PHP_EOL);
                  fclose($csv_import_log_file);
              }
              else{
                  /** UPDATE THE CONTENT */ 
                  if($entity_content->hasTranslation($langcode)){
                    $entity_content = $entity_content->getTranslation($langcode);
                    foreach ($fields_arr as $key => $field_value) {
                      $entity_content->set($key,$field_value);
                    }
                    // $entity_content->set('moderation_state',$moderation)->save();
                    $entity_content->save();
                    $context['results']['count_update'][] = 'row # '.strval($row);
                  }
                  else{
                    // $entity_content->addTranslation($langcode,$fields_arr)->set('moderation_state',$moderation)->save();
                    $entity_content->addTranslation($langcode,$fields_arr)->save();
                    $context['results']['count_update'][] = 'row # '.strval($row);
                  }

                  $pathauto->createEntityAlias($entity_content,'bulkupdate');                  

                  if($entity_type == 'taxonomy_term'){
                    $context['results']['tid_arr'][$id] = $id;
                  }
              }
            }

            $context['sandbox']['progress']++ ;
            $context['sandbox']['current_node']++;
            $context['message'] = t('Importing @content_type,Total Items : @total  , Current index: @start - @end , RAM : @ram', [
              '@content_type' => $entity_type_bundle,
              // '@row'          => strval($row),
              '@total'        => strval($total_array),
              '@start'        => $start_offset,
              '@end'          => $end_offset,
              '@ram'          => $ram_usage
            ]);   

            $context['results']['langcode'][$langcode] = $langcode; // store detected language for auto generate json

       }

      if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
        $context['finished'] = $context['sandbox']['progress'] > $context['sandbox']['max']; 
        $context['results']['logger_csv_filename'] = $logger_csv_filename;
      }
    

    \Drupal::logger('CSV IMPORT')->notice($start_offset.' out of '.$end_offset.' '.$ram_usage);
  }

  /**
   * {@inheritdoc}
   */
  public function finished($success, $results, array $operations) {
    $message     = '';
    $entity_type = $this->configuration['entity_type'];
    $bundle    = $this->configuration['entity_type_bundle'];
    $langcodes = $results['langcode'];
    $filename  = $results['logger_csv_filename'];
    $total_add    = !empty($results['count_add'])    ? count($results['count_add']) : '0';
    $total_update = !empty($results['count_update']) ? count($results['count_update']) : '0';
    $total_delete = !empty($results['count_delete']) ? count($results['count_delete']) : '0';
    $total_duplict= !empty($results['count_duplicate']) ? count($results['count_duplicate']) : '0';

    $this->logs('Done Import.Process for processing queues');

    if ($success) {
      $message = $this->t('@total_add items added , @total_update items updated , @total_delete items deleted',
      [
        '@total_add'     => $total_add,
        '@total_update'  => $total_update,
        '@total_delete'  => $total_delete,
        '@total_delete'  => $total_delete,
        '@total_duplicate' => $total_duplict,
      ]);
      $this->logs('Summary '.$bundle.' : TOTAL ADD:'.$total_add.', TOTAL UPDATE:'.$total_update.', TOTAL DELETE:'.$total_delete.', TOTAL DUPLICATE:'.$total_duplict);
    }
    $this->logger->setCsvUri($filename)->endLog();
    $this->messenger()->addMessage($message);
    $this->logs('End Import , content-type:'.$bundle);
  }

  public function logs($logs){
       $date = date("F d,Y h:i:s A");
       $file = fopen('private://deleted_content_logs.txt','a');
       fwrite($file,$date.' : '.$logs.PHP_EOL);
       fclose($file);
  }
}
