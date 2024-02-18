<?php

namespace Drupal\rboot_engine;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\paragraphs\ParagraphInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Service description.
 */
class RbootEngineService {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The logger channel factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  protected $id;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Constructs a RbootEngineService object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger channel factory.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RequestStack $request_stack, LoggerChannelFactoryInterface $logger, EntityFieldManagerInterface $entity_field_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->requestStack = $request_stack;
    $this->logger = $logger;
    $this->entityFieldManager = $entity_field_manager;
  }

  public function setID($id) {
    $this->id = $id;
    return $this;
  }
  /**
   * Method description.
   */
  public function getFields() {
    $entity_field_manager->getStorage('paragraphs');
  }

  public function getIcons() {
    $connection = \Drupal::database();
    $query = $connection->select('file_usage', 'f');
    $query->condition('f.type', 'paragraphs_type');
    $query->addField('f', 'fid');
    $query->addField('f', 'id');
    $result = $query->execute();
    $files = $result->fetchall();
    $file_entity = $this->entityTypeManager->getStorage('file');
    $icons = [];
    foreach($files as $file) {
      $fid = $file->fid;
      $pid = $file->id;
      $uri = $file_entity->load($fid)->getFileUri();
      $icons[] = [
        'uri' => $uri,
        'pid' => $pid
      ];
    }
    return $icons;
  }

  public function getNodeComponents($nid) {
    $node = $this->entityTypeManager->getStorage('node')->load($nid);
    $components = $node->field_components;
    $component_ids = [];
    foreach ($components as $component) {
      $component_ids[] = $component->target_id;
    }
    return $component_ids;
  }

  public function getGroupComponent($machine_name, $id) {
    $component = $this->entityTypeManager->getStorage('paragraph')->load($nid);
    $component->$machine_name->getFieldDefinition()->get('field_type');
  }

  public function getEntityView($component_type) {
    $view_mode = $this->entityTypeManager->getStorage('entity_view_display')->load('paragraph.' . $component_type . '.default');
  }

  public function getFieldSettings($component_type) {
    $settings = $this->entityTypeManager->getStorage('entity_view_display')->load('paragraph.' . $component_type . '.default')->toArray();
    $id = $settings['bundle'];
    $fields = $settings['content'];
    $field_group = $settings['third_party_settings']['field_group'];
    return [
      'fields' => $fields,
      'attributes' => $field_group
    ];
  }

  public function getNodeData() {
    if (empty($this->id)) {
      return False;
    }
    $data = [];
    $nid = $this->id;
    $node = $this->entityTypeManager->getStorage('node')->load($nid);
    $paragraph = $this->entityTypeManager->getStorage('paragraph');
    $banner = $paragraph->load($node->field_component_banner->target_id);

    $data = [
      'id' => $nid,
      'type' => 'node',
      'title' => $node->title->value
    ];

    $data['component_banner'] = [
      'id' => $node->field_component_banner->target_id,
      'machine_name' => $banner->getType(),
      'type' => 'paragraph',
      'group' => TRUE,
      'attributes' => []
    ];

    foreach ($node->field_components as $key => $pid) {
      $component = $paragraph->load($pid->target_id);
      $data['component_body'][] = [
        'id' => $pid->target_id,
        'machine_name' => $component->getType(),
        'type' => 'paragraph',
        'group' => TRUE,
        'attributes' => [],
      ];
    }
    return $data;
  }

  public function getParagraphData($pid) {
    $paragraph = $this->entityTypeManager->getStorage('paragraph')->load($pid);
    $data = [];
    $data = [
      'id' => $pid,
      'entity' => $paragraph,
      'type' => 'paragraph',
      'machine_name' => $paragraph->getType(),
      'attributes' => $this->mapFields($paragraph),
      'editable' => TRUE,
      'list' => []
    ];
    return $data;
  }

  protected function mapFields(ParagraphInterface $paragraph) {
    $data = [];
    $dependencies = $this->getFieldSettings($paragraph->getType());
    $attributes = $dependencies['attributes'];
    foreach ($attributes as $key => $value) {
      $data[$value['weight']] = [
        'machine_key' => $key,
        'children' => $value['children'],
        'wrapper' => $this->detectWrapper($value['children']),
        'attributes' => [
          'element' => $value['format_settings']['element'],
          'class' => $value['format_settings']['classes']
        ],
      ];
    }
    ksort($data, SORT_NUMERIC);
    return $data;
  }

  protected function detectWrapper($children) {
    $flag = FALSE;
    foreach ($children as $child) {
      $flag = !str_contains($child, 'field_');
    }
    return $flag;
  }

}
