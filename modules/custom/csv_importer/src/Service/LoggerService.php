<?php

namespace Drupal\csv_importer\Service;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\datarouter\Service\LabelProvider;

use Symfony\Component\HttpFoundation\RequestStack;
/**
 * Class DataProviderService
 *
 * @package Drupal\datarouter\Service
 */
class LoggerService {
  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $content;

  protected $field;

  protected $bundle;

  protected $entityType;

  protected $tmpfile_uri;

  protected $csv_uri;

  protected $csv_keys;

  protected $entityTypeManager;

  protected $entityFieldManager;

  protected $request;

  protected $default_langcode;

  protected $current_language;

  protected $language_interface;

  const JAPAN_TIMEZONE = '+9';

  const PATH           = 'private://import_logs/';

  const NODE_TITLE     = 'PartNumber';

  const TERM_TITLE     = 'Series';

  const MISSING_URL    = 'csv_import_missing_file_url';

  const BTN_STATE      = 'importer_form_btn_state_missing';

  const INVALID_FIELDS = ['vid','type','revision_uid','uid','menu_link','revision_user','parent','moderation_state','content_translation_uid'];
  /**
   * DataProviderService constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\Core\Language\LanguageManager $current_language
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager,EntityFieldManagerInterface $entityFieldManager, LanguageManager $language,RequestStack $request){
      $this->entityTypeManager  = $entityTypeManager;
      $this->entityFieldManager = $entityFieldManager;
      $this->default_langcode   = $language->getDefaultLanguage()->getId();
      $this->current_language   = $language->getCurrentLanguage()->getId();
      $this->language_interface = $language;
      $this->request            = $request->getCurrentRequest();        
  }

  public function setContent($content){
    $this->content = $content;
    return $this;
  }

  /**
   * @param must be associative array ['machine_name' => 'field_value']
   * */ 
  public function setField($field){
    $this->field = $field;
    return $this;
  }

  public function setBundle($bundle){
    $this->bundle = $bundle;
    return $this;
  }

  public function setEntityType($entityType){
    $this->entityType = $entityType;
    return $this;
  }

  public function setTmpUri($tmpfile_uri){
    $this->tmpfile_uri = $tmpfile_uri;
    return $this;
  }

  public function setCsvUri($csv_uri){
    $this->csv_uri = $csv_uri;
    return $this;
  }

  public function setCsvKeys($csv_keys){
    $this->csv_keys = $csv_keys;
    return $this;
  }

  /**
   * @param used by ImporterBase.php
   * */ 
  public function endLog(){
    $filename = $this->csv_uri;
    \Drupal::service('state')->set(self::MISSING_URL,$filename);
    // \Drupal::service('state')->set(self::BTN_STATE,1);
  }

  /**
   * @param used by ImporterBase.php
   * @param bundle
   * @param @entityType
   * @return [filename,keys]
   * set the header of csv file
   * */ 
  public function startLog(){
    $filename = $this->getFileName();
    $keys     = $this->getKeys();
    $csv_logs = fopen($filename, 'w');
    fputcsv($csv_logs,$keys);
    fclose($csv_logs);
    return [
            'filename' => $filename,
            'keys'     => $keys
           ];
  }

  /**
   * @param used by ImporterBase.php
   * @param list all the fields that are missing on single content
   * */ 
  public function appendFieldLog(){
      $tmpfile_uri = $this->tmpfile_uri;
      $field       = $this->field;
      $data_field  = [];
      $tmp_file    = fopen($tmpfile_uri,'r'); // read from prev temp file
      $data        = fread($tmp_file,100000);
      fclose($tmp_file);
      if(!empty($data)){
        $data_field = json_decode($data,TRUE);
      }

      $data_field[] = $field;    
      $data_field   = json_encode($data_field);
      $tmp_file     = fopen($tmpfile_uri,'w');
      fwrite($tmp_file,$data_field);
      fclose($tmp_file);
  }

  /**
   * @param used by ImporterBase.php
   * */ 
  public function appendRowLog(){
    $content     = $this->content;
    $entityType  = $this->entityType;
    $tmpfile_uri = $this->tmpfile_uri;
    $csv_uri     = $this->csv_uri;
    $csv_keys    = $this->csv_keys;

    $tmp_file    = fopen($tmpfile_uri,'r');
    $data        = fread($tmp_file,100000);
    $data        = json_decode($data,TRUE); 
    fclose($tmp_file);
    
    if(empty($data)){
      return FALSE;
    }
    
    if($entityType == 'node'){
      $data[] = [ self::NODE_TITLE => $content['title'] ];
    }

    if($entityType == 'taxonomy_term'){
      $data[] = [ self::TERM_TITLE => $content['name'] ];
    }

    $csv_uri_file = fopen($csv_uri,'a');
    fputcsv($csv_uri_file,$this->preprocessData($data,$csv_keys));
    fclose($csv_uri_file);
    return TRUE;
  }

  private function preprocessData($data,$keys){
    $tmp     = [];
    $tmp_all = [];
    foreach ($data as $dat) {
      $tmp[array_keys($dat)[0]] = array_values($dat)[0]; 
    }

    foreach ($keys as $key ) {
       $tmp_all[$key] = !empty($tmp[$key]) ? $tmp[$key] : "";
    }
    return $tmp_all;
  }

  private function getFileName(){
    $bundle = $this->bundle;
    
    $date   = new DrupalDateTime('now',self::JAPAN_TIMEZONE);
    
    $data['year']  = $date->format('Y');
    $data['month'] = $date->format('m');
    $data['day']   = $date->format('d');
    $data['hour']  = $date->format('H');
    $data['min']   = $date->format('i');
    $data['sec']   = $date->format('s');
    $filename      = self::PATH.$bundle.'__'
                    .$data['year'].
                     '-'
                    .$data['month'].
                     '-'
                    .$data['day'].
                     '_'
                    .$data['hour'].
                    '_'
                    .$data['min'].
                    '_'
                    .$data['sec'].'.csv';    
    return $filename;
  }

  /**
   * @param get the machine names that has file and image data type
   * */ 
  private function getKeys(){
    $entity     = $this->entityFieldManager;
    $bundle     = $this->bundle;
    $entityType = $this->entityType;
    $fields     = $entity->getFieldDefinitions($entityType,$bundle);
   
    $keys    = [];
    if($entityType == 'node'){
      $keys[]  = self::NODE_TITLE;
    }
    if($entityType == 'taxonomy_term'){
      $keys[]  = self::TERM_TITLE;
    }

    foreach ($fields as $machine_name => $field) {
      $type = $field->getType();
      if($type == 'file' ||  $type == 'image' || $type == 'entity_reference'){
        if(in_array($machine_name,self::INVALID_FIELDS)){
          continue;
        }
        $keys[] = $machine_name;
      }
    }
    return $keys;
  }
  
}
