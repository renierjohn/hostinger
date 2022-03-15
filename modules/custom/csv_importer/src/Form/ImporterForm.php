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
class ImporterForm extends FormBase {

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

  const FIELD_MIDDLE_CATGRY = [
      'connector'                   => 'field_con_middle_category',
      'saw_devices'                 => 'field_saw_middle_category',
      'power_semiconductor_devices' => 'field_pow_middle_category',
      'crystal_devices'             => 'field_cry_middle_category',
      'capacitors'                  => 'field_cap_middle_category'
  ];

  const FIELD_SMALL_CATGRY = [
      'connector'                   => 'field_con_small_category',
      'saw_devices'                 => 'field_saw_series',
      'power_semiconductor_devices' => 'field_pow_small_category',
      'crystal_devices'             => 'field_cry_series',
      'capacitors'                  => 'field_cap_series'
  ]; 

  const VALID_ENITY_TYPE = ['connector','saw_devices','power_semiconductor_devices','crystal_devices','capacitors','small_product_category','middle_product_category']; 

  const BTN_STATE   = 'importer_form_btn_state_missing';

  const MISSING_URL = 'csv_import_missing_file_url';

  const MIDDLE_CATEGORY = 'middle_product_category';

  const SMALL_CATEGORY  = 'small_product_category';

  const STATE_KEY_JSONFILE    = 'json_file_state';  
  
  const STATE_KEY_JSON_TMP_STATUS    = 'json_file_state_tmp';

  const STATE_KEY_JSON_TMP_DATA      = 'json_file_state_data';
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
    return 'csv_importer_form';
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

  
    $form['importer']['status'] = [
      '#type' => 'select',
      '#title' => $this->t('Choose Status (Export Only)'),
      '#options' => [
        'all' => t('All'),
        '1'   => t('Published (1)'),
        '0'   => t('Unpublished (0)'),
      ],
      '#default_value' => '1',
      '#weight' => 0,
    ];

    $form['importer']['entity_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Choose entity type'),
      // '#required' => TRUE,
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
          // '#required' => TRUE,
          '#weight' => 5,
        ];

      }

      $options = $this->getEntityTypeImporterOptions($entity_type);

      $form['importer']['plugin_id'] = [
        '#type' => 'hidden',
        '#value' => key($options),
      ];

      if (count($options) > 1) {
        $form['importer']['plugin_id'] = [
          '#type' => 'select',
          '#title' => $this->t('Choose importer'),
          '#options' => $options,
          '#default_value' => 0,
          '#weight' => 25,
        ];
      }

      // $form['importer']['delimiter'] = [
      //   '#type' => 'select',
      //   '#title' => $this->t('Choose delimiter'),
      //   '#options' => [
      //     ',' => ',',
      //     '~' => '~',
      //     ';' => ';',
      //     ':' => ':',
      //   ],
      //   '#default_value' => ',',
      //   '#required' => TRUE,
      //   '#weight' => 10,
      // ];
    }

    $form['importer']['csv'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Choose CSV file'),
      '#autoupload' => TRUE,
      '#upload_location' => 'private://csv_import_temp/',
      '#upload_validators' => ['file_validate_extensions' => ['csv']],
      '#weight' => 10,
    ];

    $form['import'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => t('Import'),
      '#submit' => array('::importCSVData'),
    ];

    $form['export'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => t('Export'),
      '#submit' => array('::exportCsvData'),
    ];

    $filename = \Drupal::service('state')->get('csv_file_download');
    $form['download'] = [
      '#type'     => 'submit',
      '#value'    => t('Download CSV'),
      '#disabled' => $filename ? FALSE : TRUE,
      '#submit'   => ['::downloadCSV'],
    ];

    
    $form['download_missing_file'] = [
      '#type'     => 'submit',
      '#value'    => t('Download Missing Files'),
      // '#disabled' => $btn_state ? FALSE : TRUE,
      '#submit'   => ['::downloadMissingFileCSV'],
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
      if($entity_type == 'node' || $entity_type == 'taxonomy_term' || $entity_type == 'user'){
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
          // if( in_array($type->id(),self::VALID_ENITY_TYPE) ){
            $options[$type->id()] = $type->label();
          // }          
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
   * Get entity type fields.
   *
   * @param string $entity_type
   *   Entity type.
   * @param string|null $entity_type_bundle
   *   Entity type bundle.
   *
   * @return array
   *   Entity type fields.
   */
  protected function getEntityTypeFields(string $entity_type, string $entity_type_bundle = NULL) {
    $fields = [];

    if (!$entity_type_bundle) {
      $entity_type_bundle = key($this->entityBundleInfo->getBundleInfo($entity_type));
    }

    $entity_fields = $this->entityFieldManager->getFieldDefinitions($entity_type, $entity_type_bundle);
    foreach ($entity_fields as $entity_field) {
      $fields['fields'][] = $entity_field->getName();

      if ($entity_field->isRequired()) {
        $fields['required'][] = $entity_field->getName();
      }
    }

    return $fields;
  }

  /**
   * Get entity missing fields.
   *
   * @param string $entity_type
   *   Entity type.
   * @param array $required
   *   Entity required fields.
   * @param array $csv
   *   Parsed CSV.
   *
   * @return array
   *   Missing fields.
   */
  protected function getEntityTypeMissingFields(string $entity_type, array $required, array $csv) {
    $entity_definition = $this->entityTypeManager->getDefinition($entity_type);

    if ($entity_definition->hasKey('bundle')) {
      unset($required[array_search($entity_definition->getKey('bundle'), $required)]);
    }

    $csv_fields = [];

    if (!empty($csv)) {
      foreach ($csv[0] as $csv_row) {
        $csv_row = explode('|', $csv_row);
        $csv_fields[] = $csv_row[0];
      }
    }

    $csv_fields = array_values(array_unique($csv_fields));

    return array_diff($required, $csv_fields);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // migrate data code to importCSVData() function
  }

   /**
   * {@inheritdoc}
   * - this function is for importing csv files
   */
  public function importCSVData(array &$form, FormStateInterface $form_state) {
    // \Drupal::service('state')->set(self::BTN_STATE,0);
    $entity_type = $form_state->getValue('entity_type');
    $entity_type_bundle = NULL;
    $csv = current($form_state->getValue('csv'));
    // $csv_parse = $this->parser->getCsvById($csv, $form_state->getUserInput()['delimiter']);
    $csv_parse = [];
    if (isset($form_state->getUserInput()['entity_type_bundle'])) {
      $entity_type_bundle = $form_state->getUserInput()['entity_type_bundle'];
    }

    // if($entity_type == 'node'){
      //  $valid_category = $this->getValidCategory($entity_type_bundle);
      //  $mids = $valid_category[self::MIDDLE_CATEGORY];
      //  $sids = $valid_category[self::SMALL_CATEGORY];
       
      // if(!empty($mids)){
      //   foreach ($mids as $mid) {
      //     $this->alterRegisterState($mid,$entity_type_bundle,self::MIDDLE_CATEGORY);
      //   }
      // }

      // if(!empty($sids)){
      //   foreach ($sids as $sid) {
      //     $this->alterRegisterState($sid,$entity_type_bundle,self::SMALL_CATEGORY);
      //    }
      // }
    // }

    $entity_fields = $this->getEntityTypeFields($entity_type, $entity_type_bundle);
    $this->importer->createInstance($form_state->getUserInput()['plugin_id'], [
      'csv'         => [],//$csv_parse,
      'csv_entity'  => $this->parser->getCsvEntity($csv),
      'entity_type' => $entity_type,
      'entity_type_bundle' => $entity_type_bundle,
      'fields'      => $entity_fields['fields'],
      ])->process();
  }

  /**
   * {@inheritdoc}
   * - this function gets the data on the view json and pass it to the function that converts the json file into a .csv file
   */
  public function exportCsvData(array &$form, FormStateInterface $form_state){
    $result       = $form_state->getValues();
    $entity_type  = $result['entity_type'];
    $content_type = $result['entity_type_bundle'];
    $langcode     = 'en';
    $translatable = false;
    $status       = $result['status'];
    // $mids         = $result['middle_category'];
    
    if(empty($entity_type)){
      return \Drupal::messenger()->addWarning(t('Please Select Entity'));
    }

    $batch = new BatchExport($content_type,$entity_type,$langcode,$translatable);
    if($status != 'all'){
      $batch->setStatus($status);
    }
    $batch->execute();
  }

  
  public function downloadMissingFileCSV(array &$form, FormStateInterface $form_state){
    $filename = \Drupal::service('state')->get(self::MISSING_URL);
    if(!empty($filename)){
      \Drupal::service('state')->delete(self::MISSING_URL);
      return $this->downloadItemTypeExport($filename);
    }
    else{
      return \Drupal::messenger()->addWarning(t('Please Import Something'));
    }
  }
  /**
   * {@inheritdoc}
   * - this function gets the data on the view json and pass it to the function that converts the json file into a .csv file
   */
  public function downloadCSV(array &$form, FormStateInterface $form_state){
    $form_state->setValidationComplete($validation_complete = true);
    $filename = \Drupal::service('state')->get('csv_file_download');
    if(!empty($filename)){
      \Drupal::service('state')->delete('csv_file_download');
      return $this->downloadItemTypeExport($filename);
    }
  }
  /**
   * Download file.
   *
   * @param string $filename
   *   The filename.
   */
  private function downloadItemTypeExport($outputFilepath) {
    $headers = [];
    $headers = [
      'Content-Type' => 'text/csv', // Would want a condition to check for extension and set Content-Type dynamically
      'Content-Description' => 'File Download',
      'Content-Disposition' => 'attachment; filename='.basename($outputFilepath)
    ];
    // Return and trigger file donwload.
    $response =  new BinaryFileResponse($outputFilepath, 200,$headers,true);
    $response->send();exit();
  }

}
