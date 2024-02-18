<?php

namespace Drupal\aggregator\Plugin\Block;

use Drupal\aggregator\FeedStorageInterface;
use Drupal\aggregator\ItemStorageInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an 'Aggregator feed' block with the latest items from the feed.
 *
 * @Block(
 *   id = "aggregator_feed_block",
 *   admin_label = @Translation("Aggregator feed"),
 *   category = @Translation("Lists (Views)")
 * )
 */
class AggregatorFeedBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity storage for feeds.
   *
   * @var \Drupal\aggregator\FeedStorageInterface
   */
  protected $feedStorage;

  /**
   * The entity storage for items.
   *
   * @var \Drupal\aggregator\ItemStorageInterface
   */
  protected $itemStorage;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs an AggregatorFeedBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\aggregator\FeedStorageInterface $feed_storage
   *   The entity storage for feeds.
   * @param \Drupal\aggregator\ItemStorageInterface $item_storage
   *   The entity storage for feed items.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FeedStorageInterface $feed_storage, ItemStorageInterface $item_storage, LoggerChannelFactoryInterface $logger_factory = NULL) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->feedStorage = $feed_storage;
    $this->itemStorage = $item_storage;
    $this->logger = $logger_factory->get('aggregator');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage('aggregator_feed'),
      $container->get('entity_type.manager')->getStorage('aggregator_item'),
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    // By default, the block will contain 10 feed items.
    return [
      'block_count' => 10,
      'feed' => NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    // Only grant access to users with the 'access news feeds' permission.
    return AccessResult::allowedIfHasPermission($account, 'access news feeds');
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $feeds = $this->feedStorage->loadMultiple();
    $options = [];
    foreach ($feeds as $feed) {
      $options[$feed->id()] = $feed->label();
    }
    $form['feed'] = [
      '#type' => 'select',
      '#title' => $this->t('Select the feed that should be displayed'),
      '#default_value' => $this->configuration['feed'],
      '#options' => $options,
    ];
    $range = range(1, 20);
    $form['block_count'] = [
      '#type' => 'select',
      '#title' => $this->t('Number of news items in block'),
      '#default_value' => $this->configuration['block_count'],
      '#options' => array_combine($range, $range),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['block_count'] = $form_state->getValue('block_count');
    $this->configuration['feed'] = $form_state->getValue('feed');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Load the selected feed.
    if (!$feed = $this->feedStorage->load($this->configuration['feed'])) {
      return [];
    }
    $result = $this->itemStorage->getQuery()
      ->accessCheck(TRUE)
      ->condition('fid', $feed->id())
      ->range(0, $this->configuration['block_count'])
      ->sort('timestamp', 'DESC')
      ->sort('iid', 'DESC')
      ->execute();

    if ($result) {
      // Only display the block if there are items to show.
      $items = $this->itemStorage->loadMultiple($result);

      $build['list'] = [
        '#theme' => 'item_list',
        '#items' => [],
      ];
      /** @var \Drupal\aggregator\Entity\Item $item */
      foreach ($items as $item) {
        try {
          $build['list']['#items'][$item->id()] = [
            '#type' => 'link',
            '#url' => $item->buildItemUri(),
            '#title' => $item->label(),
          ];
        }
        catch (\InvalidArgumentException $exception) {
          // Handle invalid item URLs.
          $this->logger->notice("Received an invalid URL for aggregator item %item in feed %url", [
            '%item' => $item->label(),
            '%url' => $feed->getUrl(),
          ]);
        }
      }
      $build['more_link'] = [
        '#type' => 'more_link',
        '#url' => $feed->toUrl(),
        '#attributes' => ['title' => $this->t("View this feed's recent news.")],
      ];
      return $build;
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $cache_tags = parent::getCacheTags();
    if ($feed = $this->feedStorage->load($this->configuration['feed'])) {
      $cache_tags = Cache::mergeTags($cache_tags, $feed->getCacheTags());
    }
    return $cache_tags;
  }

}
