<?php

namespace Drupal\aggregator\Plugin\aggregator\processor;

use Drupal\aggregator\Entity\Item;
use Drupal\aggregator\FeedInterface;
use Drupal\aggregator\FeedStorageInterface;
use Drupal\aggregator\ItemsImporter;
use Drupal\aggregator\Plugin\AggregatorPluginSettingsBase;
use Drupal\aggregator\Plugin\ProcessorInterface;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBaseTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a default processor implementation.
 *
 * Creates lightweight records from feed items.
 *
 * @AggregatorProcessor(
 *   id = "aggregator",
 *   title = @Translation("Default processor"),
 *   description = @Translation("Creates lightweight records from feed items.")
 * )
 */
class DefaultProcessor extends AggregatorPluginSettingsBase implements ProcessorInterface, ContainerFactoryPluginInterface {

  use ConfigFormBaseTrait;

  /**
   * Contains the configuration object factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The logger.channel.aggregator service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The keyvalue.aggregator service.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueFactoryInterface
   */
  protected $keyValue;

  /**
   * Constructs a DefaultProcessor object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The configuration factory object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity_type.manager service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.channel.aggregator logger service.
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface
   *   The keyvalue.aggregator service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config, EntityTypeManagerInterface $entity_type_manager, DateFormatterInterface $date_formatter, MessengerInterface $messenger, LoggerInterface $logger, KeyValueFactoryInterface $key_value) {
    $this->configFactory = $config;
    $this->entityTypeManager = $entity_type_manager;
    $this->dateFormatter = $date_formatter;
    $this->messenger = $messenger;
    $this->logger = $logger;
    $this->keyValue = $key_value;
    // @todo Refactor aggregator plugins to ConfigEntity so merging
    //   the configuration here is not needed.
    parent::__construct($configuration + $this->getConfiguration(), $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('date.formatter'),
      $container->get('messenger'),
      $container->get('logger.channel.aggregator'),
      $container->get('keyvalue.aggregator')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['aggregator.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('aggregator.settings');
    $processors = $config->get('processors');
    $info = $this->getPluginDefinition();
    $counts = [3, 5, 10, 15, 20, 25];
    $items = array_map(function ($count) {
      return $this->formatPlural($count, '1 item', '@count items');
    }, array_combine($counts, $counts));
    $intervals = [3600, 10800, 21600, 32400, 43200, 86400, 172800, 259200, 604800, 1209600, 2419200, 4838400, 9676800];
    $period = array_map([$this->dateFormatter, 'formatInterval'], array_combine($intervals, $intervals));
    $period[FeedStorageInterface::CLEAR_NEVER] = $this->t('Never');

    $form['processors'][$info['id']] = [];
    // Only wrap into details if there is a basic configuration.
    if (isset($form['basic_conf'])) {
      $form['processors'][$info['id']] = [
        '#type' => 'details',
        '#title' => $this->t('Default processor settings'),
        '#description' => $info['description'],
        '#open' => in_array($info['id'], $processors),
      ];
    }

    $form['processors'][$info['id']]['aggregator_summary_items'] = [
      '#type' => 'select',
      '#title' => $this->t('Number of items shown in listing pages'),
      '#default_value' => $config->get('source.list_max'),
      '#empty_value' => 0,
      '#options' => $items,
    ];

    $form['processors'][$info['id']]['aggregator_clear'] = [
      '#type' => 'select',
      '#title' => $this->t('Discard items older than'),
      '#default_value' => $config->get('items.expire'),
      '#options' => $period,
      '#description' => $this->t('Requires a correctly configured <a href=":cron">cron maintenance task</a>.', [':cron' => Url::fromRoute('system.status')->toString()]),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $config = [];
    if ($this->configuration['items']['expire'] !== $form_state->getValue('aggregator_clear')) {
      $this->deleteFeedHashes();
      $config['items']['expire'] = $form_state->getValue('aggregator_clear');
    }

    $config['source']['list_max'] = $form_state->getValue('aggregator_summary_items');
    // @todo Refactor aggregator plugins to ConfigEntity so this is not needed.
    $this->setConfiguration($config);
  }

  /**
   * Deletes the hashes for all feeds from state.
   */
  protected function deleteFeedHashes() {
    $query = $this->entityTypeManager->getStorage('aggregator_feed')->getQuery();
    $feed_ids = $query->accessCheck(FALSE)->execute();
    foreach ($feed_ids as $id) {
      $this->keyValue->get($id)->delete(ItemsImporter::AGGREGATOR_HASH_KEY);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function process(FeedInterface $feed) {
    if (!is_array($feed->items)) {
      return;
    }
    foreach ($feed->items as $item) {
      // @todo The default entity view builder always returns an empty
      //   array, which is ignored in aggregator_save_item() currently. Should
      //   probably be fixed.
      if (empty($item['title'])) {
        continue;
      }

      // Save this item. Try to avoid duplicate entries as much as possible. If
      // we find a duplicate entry, we resolve it and pass along its ID is such
      // that we can update it if needed.
      if (!empty($item['guid'])) {
        $values = ['fid' => $feed->id(), 'guid' => $item['guid']];
      }
      elseif ($item['link'] && $item['link'] != $feed->link && $item['link'] != $feed->url) {
        $values = ['fid' => $feed->id(), 'link' => $item['link']];
      }
      else {
        $values = ['fid' => $feed->id(), 'title' => $item['title']];
      }

      // Try to load an existing entry.
      if ($entry = $this->entityTypeManager->getStorage('aggregator_item')->loadByProperties($values)) {
        $entry = reset($entry);
      }
      else {
        $entry = Item::create(['langcode' => $feed->language()->getId()]);
      }
      if ($item['timestamp']) {
        $entry->setPostedTime($item['timestamp']);
      }

      // Make sure the item title and author fit in the 255 varchar column.
      $entry->setTitle(Unicode::truncate($item['title'], 255, TRUE, TRUE));
      $entry->setAuthor(Unicode::truncate($item['author'], 255, TRUE, TRUE));

      $entry->setFeedId($feed->id());
      $entry->setLink($item['link']);
      $entry->setGuid($item['guid']);

      $description = '';
      if (!empty($item['description'])) {
        $description = $item['description'];
      }
      $entry->setDescription($description);

      try {
        $entry->save();
      }
      catch (EntityStorageException $e) {
        $this->logger->error("There was a problem while saving an item from the %feed_title feed.  The item's GUID was %item_guid.  Error message: @message", [
          '%feed_title' => $feed->label(),
          '%item_guid' => $item['guid'],
          '@message' => $e->getMessage(),
        ]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function delete(FeedInterface $feed) {
    $item_storage = $this->entityTypeManager->getStorage('aggregator_item');
    if ($items = $item_storage->loadByFeed($feed->id())) {
      $item_storage->delete($items);
    }
    // @todo This should be moved out to caller with a different message maybe.
    $this->messenger->addStatus($this->t('The news items from %site have been deleted.', ['%site' => $feed->label()]));
  }

  /**
   * Implements \Drupal\aggregator\Plugin\ProcessorInterface::postProcess().
   *
   * Expires items from a feed depending on expiration settings.
   */
  public function postProcess(FeedInterface $feed) {
    $item_storage = $this->entityTypeManager->getStorage('aggregator_item');
    $aggregator_clear = $this->configuration['items']['expire'];

    if ($aggregator_clear != FeedStorageInterface::CLEAR_NEVER) {
      // Delete all items that are older than flush item timer.
      $age = REQUEST_TIME - $aggregator_clear;
      $result = $item_storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('fid', $feed->id())
        ->condition('timestamp', $age, '<')
        ->execute();
      if ($result) {
        $entities = $item_storage->loadMultiple($result);
        $item_storage->delete($entities);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configFactory->get('aggregator.settings')->get();
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $config = $this->config('aggregator.settings');
    foreach ($configuration as $key => $value) {
      $config->set($key, $value);
    }
    $config->save();
  }

}
