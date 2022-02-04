<?php

namespace Drupal\csv_importer\Form;

use Drupal\Core\State\State;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\csv_importer\ParserInterface;
use Drupal\csv_importer\Plugin\ImporterManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\csv_importer\BatchExport;

/**
 * Provides CSV importer form.
 */
class BarkotaForm extends FormBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity field manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The entity bundle info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityBundleInfo;

  /**
   * The parser service.
   *
   * @var \Drupal\csv_importer\Parser\ParserInterface
   */
  protected $parser;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The importer plugin manager service.
   *
   * @var \Drupal\csv_importer\Plugin\ImporterManager
   */
  protected $importer;
  /**
   * The importer plugin manager service.
   *
   * @var \Drupal\csv_importer\Plugin\ImporterManager
   */
  protected $state;

  const BASE_URL  = 'https://barkota-reseller-php-prod-4kl27j34za-uc.a.run.app/ob';

  const ENDPOINTS = ['/routes/passageenabled' => '/routes/passageenabled TERM',	
  					         '/companies/all' => '/companies/all NODE'
                    ];
  const VALID_ENITY_TYPE  = ['barkota_shipping_vessel','route_vessels'];

  const TYPE_VESSEL       = 'type_vessel';

  const TYPE_ROUTE        = 'type_route';

  const TYPE_VESSEL_ROUTE = 'type_vessel_route';
  /**
   * ImporterForm class constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_bundle_info
   *   The entity bundle info service.
   * @param \Drupal\csv_importer\Parser\ParserInterface $parser
   *   The parser service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\csv_importer\Plugin\ImporterManager $importer
   *   The importer plugin manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager, EntityTypeBundleInfoInterface $entity_bundle_info, ParserInterface $parser, RendererInterface $renderer, ImporterManager $importer,State $state) {
    $this->entityTypeManager  = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->entityBundleInfo   = $entity_bundle_info;
    $this->parser   = $parser;
    $this->renderer = $renderer;
    $this->importer = $importer;
    $this->state    = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('csv_importer.parser'),
      $container->get('renderer'),
      $container->get('plugin.manager.importer'),
      $container->get('state')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'barkota_importer_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $form['importer'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'csv-importer',
      ],
    ];

    $form['importer']['endpoint'] = [
	      '#type' => 'select',
	      '#title' => $this->t('Endpoint'),
	      '#options' => self::ENDPOINTS,
	      '#weight' => 0,
	  ];

    $form['importer']['entity_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Choose entity type'),
      '#required' => TRUE,
      '#options' => $this->getEntityTypeOptions(),
      '#weight' => 0,
      '#ajax' => [
        'callback' => [$this, 'getContentEntityTypesAjaxForm'],
        'wrapper' => 'csv-importer',
        'event' => 'change',
      ],
    ];

    if ($entity_type = $form_state->getValue('entity_type')) {

      if ($options = $this->getEntityTypeBundleOptions($entity_type)) {
        $form['importer']['entity_type_bundle'] = [
          '#type' => 'select',
          '#title' => $this->t('Choose entity bundle'),
          '#options' => $options,
          '#weight' => 5,
        ];

      }
    }

    $form['import'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => t('Import'),
      '#submit' => array('::importDATA'),
    ];

    return $form;
  }

  /**
   * Entity type AJAX form handler.
   */
  public function getContentEntityTypesAjaxForm(array &$form, FormStateInterface $form_state) {
    return $form['importer'];
  }

  /**
   * Get entity type options.
   *
   * @return array
   *   Entity type options.
   */
  protected function getEntityTypeOptions() {
    $options = [];
    $plugin_definitions = $this->importer->getDefinitions();
    $options = [''=>'Select'];
    foreach ($plugin_definitions as $definition) {
      $entity_type = $definition['entity_type'];
      if($entity_type == 'node' || $entity_type == 'taxonomy_term'){
        if ($this->entityTypeManager->hasDefinition($entity_type)) {
          $entity = $this->entityTypeManager->getDefinition($entity_type);
          $options[$entity_type] = $entity->getLabel();
        }
      }
    }

    return $options;
  }

  /**
   * Get entity type bundle options.
   *
   * @param string $entity_type
   *   Entity type.
   *
   * @return array
   *   Entity type bundle options.
   */
  protected function getEntityTypeBundleOptions(string $entity_type) {
    $options = [];
    $entity = $this->entityTypeManager->getDefinition($entity_type);

    if ($entity && $type = $entity->getBundleEntityType()) {
      $types = $this->entityTypeManager->getStorage($type)->loadMultiple();

      if ($types && is_array($types)) {
        foreach ($types as $type) {
          if( in_array($type->id(),self::VALID_ENITY_TYPE) ){
            $options[$type->id()] = $type->label();
          }          
        }
      }
    }

    return $options;
  }

  /**
   * Get entity importer plugin options.
   *
   * @param string $entity_type
   *   Entity type.
   *
   * @return array
   *   Entity importer plugin options.
   */
  protected function getEntityTypeImporterOptions(string $entity_type) {
    $plugin_definitions = $this->importer->getDefinitions();
    $entity_type_importers = array_keys(array_combine(array_keys($plugin_definitions), array_column($plugin_definitions, 'entity_type')), $entity_type);

    if ($entity_type_importers && is_array($entity_type_importers)) {
      $plugin_definitions = array_intersect_key($plugin_definitions, array_flip($entity_type_importers));

      foreach ($plugin_definitions as $plugin_id => $plugin_defintion) {
        $options[$plugin_id] = $plugin_defintion['label'];
      }
    }

    return $options;
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // migrate data code to importDATA() function
  }

   /**
   * {@inheritdoc}
   * - this function is for importing csv files
   */
  public function importDATA(array &$form, FormStateInterface $form_state) {
	    $values 	   = $form_state->getValues();
	    $endpoint 	 = $values['endpoint'];
	    $entity_type = $values['entity_type'];
	    $bundle   	 = $values['entity_type_bundle'];

      $params = [];
      $method = 'GET';
      if($entity_type == 'node' && $endpoint == '/companies/all'){
	     $import_type = self::TYPE_VESSEL;
       $contents = \Drupal::service('csv_importer.remote')->remoteRequestCurl($method,self::BASE_URL.$endpoint,$params);
      }


      if($entity_type == 'taxonomy_term' && $endpoint == '/routes/passageenabled'){
       $method  = 'POST';
       $import_type = self::TYPE_ROUTE;
	 	   $contents = \Drupal::service('csv_importer.remote')->remoteRequestCurl($method,self::BASE_URL.$endpoint,$params);
      }

      if($entity_type == 'node' && $endpoint == '/routes/passageenabled'){
         $method = 'POST';
         $params = [];
         $import_type = self::TYPE_VESSEL_ROUTE;
         $contents    = $this->getShippingVessels();
      }


	 	 foreach($contents as $data){
	          $process['operations'][] = [
	              [$this, 'importByBatchFile'],[$data,$import_type,$entity_type,$bundle]
	          ];               
	     }
	     
     $process['finished'] = [$this, 'finished'];
     batch_set($process); 
  }

	public function importByBatchFile($data,$import_type,$entity_type,$bundle,&$context) {
	    if (empty($context['sandbox'])) {
	      $context['sandbox'] = [];
	      $context['sandbox']['progress'] = 0;
	      $context['sandbox']['current_node'] = 0;
	      $context['sandbox']['max'] = 0;
	      $context['finished'] = FALSE;
	    }

      if($import_type == self::TYPE_VESSEL){
        $this->importVesselAndRoute($data,$entity_type,$import_type);
      }

      if($import_type == self::TYPE_ROUTE){
        $origin = array_values($data)[0];
        $destination = array_values($data)[1];
        $routes[] = $origin;
        foreach ($routes as $route) {
          $this->importVesselAndRoute($route,$entity_type,$import_type);
        }
      }

      if($import_type == self::TYPE_VESSEL_ROUTE){
        $this->importVesselWithRoute($data);
      }

	    $context['sandbox']['progress']++ ;
      $context['sandbox']['current_node']++;
        
      if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
        $context['finished'] = $context['sandbox']['progress'] > $context['sandbox']['max']; 
    	}
  }

  public function finished($success, $results, array $operations) {
    \Drupal::messenger()->addMessage('Done');
  }

  /**
   * Batch
   * */ 
  private function importVesselAndRoute($data,$entity_type,$import_type){

      $field_map = [self::TYPE_VESSEL => 
                [
                  'name'        => 'title',
                  'id'          => 'field_uuid',
                  'code'        => 'field_code',
                  'displayName' => 'field_display_name',
                  'logo'        => 'field_logo'
                ],
            self::TYPE_ROUTE => 
              [
                 'name'        => 'name',
                 'id'          => 'field_id',
                 'code'        => 'field_code',
              ],
      ];

      $bundle_map = [
        self::TYPE_VESSEL => 'barkota_shipping_vessel',
        self::TYPE_ROUTE  => 'route_vessels'
      ];

      $bundle_type_map = [
        'node'          => 'type',
        'taxonomy_term' => 'vid',
      ];

      $fields = [];
      $bundle = $bundle_map[$import_type];

      $fields[$bundle_type_map[$entity_type]] = $bundle;
      foreach ($data as $key => $value) {
        if(!empty($field_map[$import_type][$key])){
          $fields[$field_map[$import_type][$key]] = $value;   
        }
      }

      $query = $this->entityTypeManager->getStorage($entity_type)->getQuery();
      $query->condition($bundle_type_map[$entity_type],$bundle);
      $query->condition('field_code',$fields['field_code']);
      $ids = $query->execute();
      
      if(!empty($ids)){ // updsate content
        $id  = reset($ids);
        $entityContent = $this->entityTypeManager->getStorage($entity_type)->load($id);
        foreach ($fields as $machine_name => $value) {
          $entityContent->set($machine_name,$value);
        }
        $entityContent->save();
      }
      else{ //add content if not exists
         $this->entityTypeManager->getStorage($entity_type)->create($fields)->save();
      }
  }

  /**
   * Batch
   * */ 
  private function importVesselWithRoute($data){
    $node = $this->entityTypeManager->getStorage('node')->load($data);
    $uuid = $node->field_uuid->value;
    $params = ['companyId' => $uuid ];
    $contents = \Drupal::service('csv_importer.remote')->remoteRequestCurl('POST',self::BASE_URL.'/routes/passageenabled',$params);
    $origins  = [];
    $dests    = [];

    foreach ($contents as $content) {
      $origins[] = $content['origin']['code'];
      foreach ($content['destinations'] as $destination) {
        $dests[]   = $destination['code'];
      }
    }
    
    $origins = array_unique($origins);
    $dests   = array_unique($dests);
    
    $origins_tid = [];
    $dests_tid   = []; 
    foreach ($origins as $origin_code) {
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->getQuery();
      $term->condition('vid','route_vessels');
      $term->condition('field_code',$origin_code);
      $tid = $term->execute();
      if(!empty($tid)){
        $origins_tid[] = reset($tid);
      }
    }

    foreach ($dests as $dest_code) {
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->getQuery();
      $term->condition('vid','route_vessels');
      $term->condition('field_code',$dest_code);
      $tid = $term->execute();
      if(!empty($tid)){
        $dests_tid[] = reset($tid);
      }
    }
// ksm($origins_tid,$dests_tid);
    $node->set('field_vessel_origin',$origins_tid);
    $node->set('field_vessel_destination',$dests_tid);
    if(!empty($origins_tid) || !empty($dests_tid)){
      $node->save();
    }

  }
  

  /**
   * Batch
   * */ 
  private function getShippingVessels(){
      $query = $this->entityTypeManager->getStorage('node')->getQuery();
      $query->condition('type','barkota_shipping_vessel');
      $query->condition('status',1);
      $ids = $query->execute();
      return array_values($ids);
  }



}
