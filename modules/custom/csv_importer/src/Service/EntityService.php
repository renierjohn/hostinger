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

  protected $entity_type = 'node';

  const UNSET_FIELDS = ['bundle','uuid','vid','type','revision_timestamp','description',
                        'revision_uid','revision_log','uid','created','changed',
                        'promote','sticky','default_langcode','revision_default',
                        'revision_translation_affected','path','moderation_state','content_translation_source',
                        'content_translation_outdated','menu_link',
                        'parent','revision_id','revision_created','revision_user','revision_log_message','weight','content_translation_uid','content_translation_created','metatag'
                      ];

  const FIELD_MIDDLE_CATGRY = [
      'connector'                   => 'field_con_middle_category',
      'saw_devices'                 => 'field_saw_middle_category',
      'power_semiconductor_devices' => 'field_pow_middle_category',
      'crystal_devices'             => 'field_cry_middle_category',
      'capacitors'                  => 'field_cap_middle_category'
    ];                    

  const DEFAULT_LANGCODE =  'ja';

  const ENTITY_MEDIA  = 'media';

  const ENTITY_FILE   = 'file';

  const ENTITY_IMAGE  = 'image';

  const ENTITY_TERM   = 'taxonomy_term';

  const ENTITY_NODE   = 'node';

  const CATALOG_FIELD = 'field_s_category_catalog';

  const PUBLIC_PATH   = '/sites/default/files/';

  const SEPARATOR     = ',';

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

  
  /**
   * @param only for import csv , can detect multiple title for taxonomy and node
   * @param Used by ImporterBase.php
   * @return ['mismatch_name','id']
   * */ 
  public function getIDFromNames(){
    $names       = $this->names;
    $entity_type = $this->entity_type; 
    $handler     = $this->handlerSettings; // array ['key'=>'value']
    $status      = ['mismatch_name'=>[],'id'=>[]];
    if(empty($names)){
      return FALSE;
    }
    $names   = explode(self::SEPARATOR,$names);    
    
    $entity  = $this->entityTypeManager->getStorage($entity_type);    
    
    $handler = reset($handler);

    foreach ($names as $name) {            

      if($entity_type == 'taxonomy_term'){ // detect from taxonomy name
          $id = $entity->getQuery()
            ->condition('name',$name,'=')
            ->condition('vid',$handler)
            ->execute();    
      }
      
      if($entity_type == 'node'){ // detect from node title
        $id = $entity->getQuery()
          ->condition('title',$name,'=')
          ->condition('type',$handler)
          ->execute();    
      }

      if(empty($id)){
        $status['mismatch_name'][] = $name;       
      }
      else{
        $status['id'][] = reset($id);
      }
    }

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
    $uri_s   = explode(self::SEPARATOR,$uri_s);    
    $entity  = $this->entityTypeManager->getStorage('file');
    foreach ($uri_s as $urix) {      
      // $uri     = str_replace('sites/default/files/','public://',$urix); 
      // $fid     = $entity->getQuery()->condition('uri',$uri)->execute();
    
      // if(empty($fid)){
      //   $status['missing_uri'][] = $urix;       
      // }
      // else{
      //   $status['fid'][] = reset($fid);
      // }

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
    if(!empty($status) || $status == '0'){
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
  * Used By ExportCSV Controller
  * node array
  * @return array field label => value
  */
  // public function loadAllCompare(){
  //   $contents        = [];
  //   $lang_interface  = $this->language_interface;
  //   $entity_type     = $this->entity_type;
  //   $langcode        = $this->langcode;
  //   $ids             = $this->ids;
  //   $items           = $this->items;
  //   $current_language = $this->current_language;
  //   if(empty($ids)){
  //     return FALSE;
  //   }

  //   $language = $lang_interface->getLanguage($langcode);
  //   $lang_interface->setConfigOverrideLanguage($language);
    
  //   $nodes    = $this->entityTypeManager->getStorage($entity_type)->loadMultiple($ids);
  //   foreach ($nodes as $node) {
  //     if($node->hasTranslation($langcode)){
  //       $tmp       = [];
  //       $labels    = $node->getFieldDefinitions();
  //       $content   = $node->getTranslation($langcode)->toArray();
  //       $bundle    = $content['type'][0]['target_id'];
  //       $valid_fields  = $this->getValidFields($bundle);
  //       $valid_fields  = $this->appendItemFields($valid_fields,$items,$labels);
  //       // $valid_fields  = $this->appendCatalogField($valid_fields,$bundle);
  //       $valid_fields[] = 'catalog'; // append catalog at the last part        
  //       foreach ($valid_fields as $valid_field) {
  //         if($valid_field == 'catalog'){
  //           $catalog = $this->getCatalog($content,$bundle);
  //           if(!empty($catalog)){
  //             $catalog = $this->getDomain().$catalog;
  //           }
  //           $tmp['Catalog'] = $catalog;
  //           continue;
  //         }
  //         $fields     = $content[$valid_field];
  //         $fields     = $this->preprocessFields($fields,$labels[$valid_field],$langcode);
  //         $labels_key = $labels[$valid_field]->getLabel();
  //         $bom        = chr(239).chr(187).chr(191);
  //         $tmp[$bom.$labels_key] = '"'.$bom.$fields.'"';
  //       }
  //       $contents[] = $tmp;   
  //     }
  //   }

  //   return $contents;
  // }

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
          $tmp[$machine_name.'|'.$labels[$machine_name]->getLabel()] = $this->preprocessFields($value,$labels[$machine_name],$langcode,$is_external);
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
  private function preprocessFields($fields,$labels,$langcode,$is_external = TRUE){  
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
      return implode(',',$urls);
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
      return implode(self::SEPARATOR,$result);
    }

    if($type == self::ENTITY_NODE){
      $nid          = [];
      $status_names = [];
      $result       = [];

      foreach ($fields as $val) {
        $nid[] = $val['target_id'];
      }

      $nodes = $this->entityTypeManager->getStorage(self::ENTITY_NODE)->loadMultiple($nid);      
      foreach ($nodes as $node) {
        $node = $node->hasTranslation($langcode) ?  $node->getTranslation($langcode) : $node;
        $result[] = $node->getTitle();
      }
      return implode(self::SEPARATOR,$result);
    }

    $tmp = [];
    foreach ($fields as $val) {
      $tmp[] = $val['value'];
    }

    return implode(self::SEPARATOR,$tmp);
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

   /**
   * @param  $validfields   - fix , array of mahine_names
   * @param  @items - dynamic , machine_names that visible from coloumn
   * @return merge arrays
   * */ 
  // private function appendCatalogField($valid_fields,$bundle){
  //    $table_fields   = LabelProvider::getTableFIeld();
  //    $valid_fields[] = $table_fields['catalog'][$bundle];
  //    return $valid_fields;
  // }

  private function getDomain(){
    $request   = $this->request;
    $domain    = $request->getHttpHost();
    $scheme    = $request->getScheme();
    return $scheme.'://'.$domain;
  }

//   private function getCatalog($content,$bundle){
//     $entity = $this->entityTypeManager;
   
//     $series = LabelProvider::getTableFIeld()['catalog'][$bundle];
//     $tid    = $content[$series] ? $content[$series][0]['target_id'] : FALSE;
//     if(empty($tid)){
//       return FALSE;
//     }
//     $series = $entity->getStorage('taxonomy_term')->load($tid);
//     if(empty($series)){
//       return FALSE;
//     }
//     $series  = $series->toArray();
//     $fid     = $series[self::CATALOG_FIELD] ? $series[self::CATALOG_FIELD][0]['target_id'] : FALSE; 
//     if(empty($fid)){
//       return FALSE;
//     }
//     $file = $entity->getStorage('file')->load($fid);
//     if(empty($file)){
//       return FALSE;
//     }
//     $file_name = $file->getFilename();
//     $file_url  = $file->getFileUri();
//     $file_url  = str_replace('public://','/',$file_url);
//     $file_size = $file->getSize();
//     $file_size = number_format($file_size/1000, 2, '.', '').' Kb';
//     return $file_url;    
//   }
}
