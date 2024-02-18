<?php

namespace Drupal\aggregator;

use Drupal\aggregator\Plugin\AggregatorPluginManager;
use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Defines an importer of aggregator items.
 */
class ItemsImporter implements ItemsImporterInterface {

  /**
   * The prefix to identify State API keys for feed hashes.
   *
   * @var string
   */
  const AGGREGATOR_HASH_KEY = 'feed_hash';

  /**
   * The aggregator fetcher manager.
   *
   * @var \Drupal\aggregator\Plugin\AggregatorPluginManager
   */
  protected $fetcherManager;

  /**
   * The aggregator processor manager.
   *
   * @var \Drupal\aggregator\Plugin\AggregatorPluginManager
   */
  protected $processorManager;

  /**
   * The aggregator parser manager.
   *
   * @var \Drupal\aggregator\Plugin\AggregatorPluginManager
   */
  protected $parserManager;

  /**
   * The aggregator.settings config object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * A logger instance.
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
   * Constructs an Importer object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\aggregator\Plugin\AggregatorPluginManager $fetcher_manager
   *   The aggregator fetcher plugin manager.
   * @param \Drupal\aggregator\Plugin\AggregatorPluginManager $parser_manager
   *   The aggregator parser plugin manager.
   * @param \Drupal\aggregator\Plugin\AggregatorPluginManager $processor_manager
   *   The aggregator processor plugin manager.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface $key_value
   *   The keyvalue.aggregator service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, AggregatorPluginManager $fetcher_manager, AggregatorPluginManager $parser_manager, AggregatorPluginManager $processor_manager, LoggerInterface $logger, KeyValueFactoryInterface $key_value) {
    $this->fetcherManager = $fetcher_manager;
    $this->processorManager = $processor_manager;
    $this->parserManager = $parser_manager;
    $this->config = $config_factory->get('aggregator.settings');
    $this->logger = $logger;
    $this->keyValue = $key_value;
  }

  /**
   * {@inheritdoc}
   */
  public function delete(FeedInterface $feed) {
    foreach ($this->processorManager->getDefinitions() as $id => $definition) {
      $this->processorManager->createInstance($id)->delete($feed);
    }
    $this->deleteHash($feed);
  }

  /**
   * {@inheritdoc}
   */
  public function refresh(FeedInterface $feed) {
    // Store feed URL to track changes.
    $feed_url = $feed->getUrl();

    // Fetch the feed.
    try {
      $success = $this->fetcherManager->createInstance($this->config->get('fetcher'))->fetch($feed);
    }
    catch (PluginException $e) {
      $success = FALSE;
      watchdog_exception('aggregator', $e);
    }

    // Store instances in an array so we don't have to instantiate new objects.
    $processor_instances = [];
    foreach ($this->config->get('processors') as $processor) {
      try {
        $processor_instances[$processor] = $this->processorManager->createInstance($processor);
      }
      catch (PluginException $e) {
        watchdog_exception('aggregator', $e);
      }
    }

    // We store the hash of feed data in the database. When refreshing a
    // feed we compare stored hash and new hash calculated from downloaded
    // data. If both are equal we say that feed is not updated.
    $hash = $success ? hash('sha256', $feed->source_string) : '';
    $has_new_content = $success && ($this->getHash($feed) != $hash);

    if ($has_new_content) {
      // Parse the feed.
      try {
        if ($this->parserManager->createInstance($this->config->get('parser'))->parse($feed)) {
          if (!$feed->getWebsiteUrl()) {
            $feed->setWebsiteUrl($feed->getUrl());
          }
          $this->setHash($feed, $hash);
          // Update feed with parsed data.
          $feed->save();

          // Log if feed URL has changed.
          if ($feed->getUrl() != $feed_url) {
            $this->logger->notice('Updated URL for feed %title to %url.', ['%title' => $feed->label(), '%url' => $feed->getUrl()]);
          }

          $this->logger->notice('There is new syndicated content from %site.', ['%site' => $feed->label()]);

          // If there are items on the feed, let enabled processors process them.
          if (!empty($feed->items)) {
            foreach ($processor_instances as $instance) {
              $instance->process($feed);
            }
          }
        }
      }
      catch (PluginException $e) {
        watchdog_exception('aggregator', $e);
      }
    }

    // Processing is done, call postProcess on enabled processors.
    foreach ($processor_instances as $instance) {
      $instance->postProcess($feed);
    }

    return $has_new_content;
  }

  /**
   * Deletes the hash for a feed from state.
   *
   * @param \Drupal\aggregator\FeedInterface $feed
   *   The feed whose hash is being cleared.
   */
  public function deleteHash(FeedInterface $feed) {
    $this->keyValue->get($feed->id())->delete(self::AGGREGATOR_HASH_KEY);
  }

  /**
   * Returns the calculated hash of the feed data, used for validating cache.
   *
   * @param \Drupal\aggregator\FeedInterface $feed
   *   The feed whose hash is being retrieved.
   *
   * @return string
   *   The calculated hash of the feed data.
   */
  public function getHash(FeedInterface $feed) {
    return $this->keyValue->get($feed->id())->get(self::AGGREGATOR_HASH_KEY);
  }

  /**
   * Sets the calculated hash of the feed data, used for validating cache.
   *
   * @param \Drupal\aggregator\FeedInterface $feed
   *   The feed for which the hash has been calculated.
   * @param string $hash
   *   A string containing the calculated hash of the feed. Must contain
   *   US ASCII characters only.
   */
  public function setHash(FeedInterface $feed, $hash) {
    $this->keyValue->get($feed->id())->set(self::AGGREGATOR_HASH_KEY, $hash);
  }
}
