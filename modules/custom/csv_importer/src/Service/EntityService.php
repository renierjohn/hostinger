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
class EntityService {
  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  protected $entityFieldManager;

  protected $request;

  protected $bundle;

  protected $ids;

  protected $langcode;

  protected $default_langcode;

  protected $language_interface;

  protected $current_language;

  protected $items;

  protected $url;

  protected $translatable;

  protected $names;

  protected $handlerSettings;

  protected $machine_name;

  protected $entity_type = 'node';

  const UNSET_FIELDS = ['bundle','uuid','vid','type','revision_timestamp','description',
                        'revision_uid','revision_log','uid','created','changed',
                        'promote','sticky','default_langcode','revision_default',
                        'revision_translation_affected','path','moderation_state','content_translation_source',
                        'content_translation_outdated','menu_link',
                        'parent','revision_id','revision_created','revision_user','revision_log_message','weight','content_translation_uid','content_translation_created','metatag'
                      ];          
  
  const REFERENCE    = [
    'field_tx_table_route' => ['type'=>'table_routes','node'=>'field_table_bus_name','name'=>'bus_name'],
  ];

  const DEFAULT_LANGCODE =  'ja';

  const ENTITY_FILE   = 'file';

  const ENTITY_IMAGE  = 'image';

  const ENTITY_LINK   = 'link';

  const ENTITY_TERM   = 'taxonomy_term';

  const ENTITY_NODE   = 'node';

  const CATALOG_FIELD = 'field_s_category_catalog';

  const PUBLIC_PATH   = '/sites/default/files/';

  const SEPARATOR     = ';';

  const LINK_DELIMITER   = '^';

  const SEPARATOR_MULTI = '|';

  /**
   * DataProviderService constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager,EntityFieldManagerInterface $entityFieldManager,LanguageManager $language,RequestStack $request){
      $this->entityTypeManager  = $entityTypeManager;
      $this->entityFieldManager = $entityFieldManager;
      $this->default_langcode   = $language->getDefaultLanguage()->getId();
      $this->current_language   = $language->getCurrentLanguage()->getId();
      $this->language_interface = $language;
      $this->request            = $request->getCurrentRequest();
      // $this->default_langcode  = \Drupal::languageManager()->getDefaultLanguage()->getId();
  }

  public function nids($ids){
    $this->ids = $ids;
    return $this;
  }

  public function tids($ids){
    $this->ids = $ids;
    return $this;
  }

  public function ids($ids){
    $this->ids = $ids;
    return $this;
  }

  public function setBundle($bundle){
    $this->bundle = $bundle;
    return $this;
  }

  public function setNames($names){
    $this->names = $names;
    return $this;
  }

  public function langcode($langcode){
    $this->langcode = $langcode;
    return $this;
  }

  public function entityType($entity_type = 'node'){
    $this->entity_type = $entity_type;
    return $this;
  }

  public function setItems($items){
    $this->items = $items;
    return $this;
  }

  public function setUri($uri){
    $this->uri = $uri;
    return $this;
  }

  public function setTranslatable($translatable){
    $this->translatable = $translatable;
    return $this;
  }

  public function setHandlerSettings($handlerSettings){
    $this->handlerSettings = $handlerSettings;
    return $this;
  }

  public function setMachineName($machine_name){
    $this->machine_name = $machine_name;
    return $this;
  }

  
  /**
   * @param only for import csv , can detect multiple title for taxonomy and node
   * @param Used by ImporterBase.php
   * @return ['mismatch_name','id']
   * */ 
  public function getIDFromNames(){
    $names       = $this->names;
    $entity_type = $this->entity_type; 
    $handler     = $this->handlerSettings; // array ['key'=>'value']
    $machine_name= $this->machine_name;
    $status      = ['mismatch_name'=>[],'id'=>[]];
    if(empty($names)){
      return FALSE;
    }
    $names   = explode(self::SEPARATOR_MULTI,$names);    
    
    $entity  = $this->entityTypeManager->getStorage($entity_type);    
    
    // $handler = reset($handler);
    $ids = [];
    foreach ($names as $name) {            
      if($entity_type == 'taxonomy_term'){ // detect from taxonomy name
          $ids = $entity->getQuery()
            ->condition('name',$name,'=')
            ->condition('vid',$handler)
            ->execute();
      }
      
      if($entity_type == 'node'){ // detect from node title
          if(in_array($machine_name,array_keys(self::REFERENCE))){
            $node_field = self::REFERENCE[$machine_name]['node'];
            $vid        = self::REFERENCE[$machine_name]['name'];
            $type       = self::REFERENCE[$machine_name]['type'];
            $tid = $this->entityTypeManager->getStorage('taxonomy_term')->getQuery()->condition('vid',$vid)->condition('name',$name)->execute();
            $tid = reset($tid);
            $id  = $this->entityTypeManager->getStorage('node')->getQuery()->condition('type',$type)->condition($node_field,$tid)->execute();
            $ids = array_values($id);
          }
          else{
            $id = $entity->getQuery()->condition('title',$name,'=')->condition('type',$handler)->execute();
              if(!empty($id)){
                  $ids[] = $id;
              }
          }
      }

      if(empty($ids)){
        $status['mismatch_name'][] = $name;       
      }
    }
    $status['id'] = $ids;
    return $status;
  }

   /**
   * @param only for import csv , can detect multiple pdf or image in single field
   * @param Used by ImporterBase.php
   * @return ['missing_uri','fid']
   * */ 
  public function getFileAssets(){
    $uri_s     = $this->uri;
    $status    = ['missing_uri'=>[],'fid'=>[]];
    if(empty($uri_s)){
      return FALSE;
    }
    $uri_s   = explode(self::SEPARATOR_MULTI,$uri_s);    
    $entity  = $this->entityTypeManager->getStorage('file');
    foreach ($uri_s as $urix) {      
      $uri = str_replace('sites/default/files/','public://',$urix);
      $uri = str_replace('/sites/default/files/','public://',$uri); 
      if(file_exists($uri)){
        $fid     = $entity->getQuery()->condition('uri',$uri)->execute();
        if(empty($fid)){
          $entity->create(['uri'=>$uri])->save();
          $fid = $entity->getQuery()->condition('uri',$uri)->execute();
        }
        $status['fid'][] = reset($fid);
        continue;
      }
      $status['missing_uri'][] = $urix;
    }

    return $status;
  }

  /**
  * Used by BatchExport.php
  * @return array nids
  */
  public function loadAllProductsByBatch($index,$limit,$content_type,$langcode,$status){
    $entity_type = 'node';
    $query = $this->entityTypeManager->getStorage($entity_type)->getQuery()
      ->condition('type',$content_type)
      ->condition('langcode',$langcode)
      ->range($index,$limit);
    
    // if(!empty($mid)){
    //    $query->condition(self::FIELD_MIDDLE_CATGRY[$content_type],$mid);
    // }
    if(!empty($status) || $status === '0'){
       $query->condition('status',$status);
    }  

    $nodes = $query->execute();
    return array_values($nodes);
  }

  /**
  * Used by BatchExport.php 
  * @return array nids
  */
  public function loadAllCategoryByBatch($index,$limit,$content_type,$langcode,$status){
    $entity_type = 'taxonomy_term';
    $query = $this->entityTypeManager->getStorage($entity_type)->getQuery()
      ->condition('vid',$content_type)
      ->condition('langcode',$langcode)
      ->range($index,$limit);

    if(!empty($status) || $status == '0'){
       $query->condition('status',$status);
    }

    $terms =  $query->execute();
    return array_values($terms);
  }

 
  /**
  * Export only . Used by BatchExport.php ,used from admin config page
  * node array
  * @return array field machine_name => value
  */
  public function loadAll(){   
    $contents         = [];
    $lang_interface   = $this->language_interface;
    $entity_type      = $this->entity_type;
    $langcode         = $this->langcode;
    $default_langcode = $this->default_langcode;
    $current_language = $this->current_language;
    $ids              = $this->ids;
    $translatable     = $this->translatable;
    $is_external      = FALSE; // flag for either include full path
    $language = $lang_interface->getLanguage($langcode);
    $lang_interface->setConfigOverrideLanguage($language);
    
    if(empty($ids)){
      return FALSE;
    }
    
    if(empty($langcode)){
       $langcode = $current_language;
    }
   
    foreach ($this->yieldLoadAllNodes() as $node) {
      if($node->hasTranslation($langcode)){
        $tmp     = [];
        $labels  = $node->getFieldDefinitions();
        $content = $node->getTranslation($langcode)->toArray();
        $content = $this->stripUnwantedFields($content);            
        foreach ($content as $machine_name => $value) {
          if(!$labels[$machine_name]->isTranslatable() && $translatable == TRUE){
              if(($machine_name != 'tid' && $entity_type == 'taxonomy_term') || ($machine_name != 'nid' && $entity_type == 'node')){
                  continue;
              }
          }         
          $tmp[$machine_name.'|'.$labels[$machine_name]->getLabel()] = $this->preprocessFields($value,$labels[$machine_name],$langcode,$is_external,$machine_name);
        }
        $contents[] = $tmp;
      }
    }
    return $contents;
  }

  private function yieldLoadAllNodes(){
    $entity_type = $this->entity_type;
    $ids      = $this->ids;
    $nodes    = $this->entityTypeManager->getStorage($entity_type)->loadMultiple($ids);
    $nodes    = array_values($nodes);
    foreach ($nodes as $node) {
      yield $node;
    }
  }

  private function stripUnwantedFields($node){
    foreach (self::UNSET_FIELDS as $field) {
      unset($node[$field]);
    }
    return $node;
  }

  private function insertFieldLabel($content,$labels){
    $tmp = [];
    foreach ($content as $machine_name => $value) {
      $key = $machine_name;
      if(!empty($labels[$machine_name])){
        $label = $labels[$machine_name]->getLabel();
        if(!empty($label)){
          $key   = $machine_name.'|'.$label;
        }
      }
      $tmp[$key] = $value;        
    }
    return $tmp;
  }

  /** 
   * @return array list machine name
   * */ 
  private function getValidFields($bundle){
       $fields = [];
       $row_fields    = LabelProvider::getRowFields();
       $table_fields  = LabelProvider::getTableFIeld();
       foreach ($row_fields as $row_field) {
         $fields[] = $table_fields[$row_field][$bundle];
       }
       return $fields;
  }

  /**
   * csv export only
   * @param $fields , array of value from node array
   * @param $labels , field difinition from single field
   * @return string value 
   * */ 
  private function preprocessFields($fields,$labels,$langcode,$is_external = TRUE,$machine_name = false){  
    $full_path = $this->getDomain();
    $settings  = [];
    if($is_external == FALSE){
      $full_path = 'sites/default/files';
    }

    if(empty($fields)){
        return FALSE;
    }

    if($labels){
      $settings  = $labels->getSettings();
    }
    $type      = isset($settings['target_type']) ? $settings['target_type'] : FALSE;

    /** start condition for pdf image*/ 
    if($type == self::ENTITY_FILE || $type == self::ENTITY_IMAGE){
      $fid  = [];
      $urls = [];

      foreach ($fields as $val) {
        $fid[] = $val['target_id'];
      }

      $files = $this->entityTypeManager->getStorage('file')->loadMultiple($fid);
      
      foreach ($files as $file) {
          $tmp_url   = $file->getFileUri();
          $file_size = $file->getSize();          
          $file_size = number_format($file_size/1000, 2, '.', '');
          $tmp_url   = str_replace('public://', $full_path.'/', $tmp_url);
          $urls[]    = $tmp_url;
      }
      return implode(self::SEPARATOR_MULTI,$urls);
    }

    if($type == self::ENTITY_TERM){
      $tid          = [];
      $status_names = [];
      $result       = [];

      foreach ($fields as $val) {
        $tid[] = $val['target_id'];
      }

      $terms = $this->entityTypeManager->getStorage(self::ENTITY_TERM)->loadMultiple($tid);      
      foreach ($terms as $term) {
        $term = $term->hasTranslation($langcode) ?  $term->getTranslation($langcode) : $term;
        $result[] = $term->getName();
      }
      return implode(self::SEPARATOR_MULTI,$result);
    }

    if($type == self::ENTITY_NODE){
      $nid          = [];
      $status_names = [];
      $result       = [];
      $results      = [];

      foreach ($fields as $val) {
        $nid[] = $val['target_id'];
      }

      $nodes = $this->entityTypeManager->getStorage(self::ENTITY_NODE)->loadMultiple($nid);      
      foreach ($nodes as $node) {
        $node = $node->hasTranslation($langcode) ?  $node->getTranslation($langcode) : $node;
        if(in_array($machine_name,array_keys(self::REFERENCE))){
            $node_field = self::REFERENCE[$machine_name]['node'];
            $tid  = $node->$node_field[0]->target_id;
            $name = $this->entityTypeManager->getStorage(self::ENTITY_TERM)->load($tid)->getName();
            $results[$tid] = $name;
        }
        else{
          $result[] = $node->getTitle();
        }
      }

      if(!empty($results)){
        $result = array_values($results);
      }

      return implode(self::SEPARATOR_MULTI,$result);
    }


    if($labels->getType() == self::ENTITY_LINK){
      $result      = [];
      foreach ($fields as $val) {
        $result[] = $val['title'].self::LINK_DELIMITER.$val['uri']; 
      }
      return implode(self::SEPARATOR_MULTI,$result);
    }

    $tmp = [];
    foreach ($fields as $val) {
      $tmp_val = !empty($val['value']) ? $val['value'] : FALSE;
      if(empty($tmp_val)){
        $tmp_val = !empty($val['uri']) ? $val['uri'] : FALSE;
      }
      $tmp[] = $tmp_val;
    }

    return implode(self::SEPARATOR_MULTI,$tmp);
  }

    /**
   * @param  $validfields   - fix , array of mahine_names
   * @param  @items - dynamic , machine_names that visible from coloumn
   * @return merge arrays
   * */ 
  private function appendItemFields($valid_fields,$items,$labels){
    $tmp_fields = [];
    if(empty($items)){
      return $valid_fields;
    }
    foreach ($items as $item) {
      if(!in_array($item,$valid_fields) && !empty($labels[$item])){
          $tmp_fields[] = $item;
      }      
    }    
    return array_merge($valid_fields,$tmp_fields);
  }

  private function getDomain(){
    $request   = $this->request;
    $domain    = $request->getHttpHost();
    $scheme    = $request->getScheme();
    return $scheme.'://'.$domain;
  }
}
