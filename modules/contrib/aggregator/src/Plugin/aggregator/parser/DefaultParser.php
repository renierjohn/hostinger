<?php

namespace Drupal\aggregator\Plugin\aggregator\parser;

use Drupal\aggregator\Plugin\AggregatorPluginSettingsBase;
use Drupal\aggregator\Plugin\ParserInterface;
use Drupal\aggregator\FeedInterface;
use Drupal\aggregator\ZfExtensionManagerSfContainer;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBaseTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Laminas\Feed\Reader\Reader;
use Laminas\Feed\Reader\Exception\ExceptionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a default parser implementation.
 *
 * Parses RSS, Atom and RDF feeds.
 *
 * @AggregatorParser(
 *   id = "aggregator",
 *   title = @Translation("Default parser"),
 *   description = @Translation("Default parser for RSS, Atom and RDF feeds.")
 * )
 */
class DefaultParser extends AggregatorPluginSettingsBase implements ParserInterface, ContainerFactoryPluginInterface {

  use ConfigFormBaseTrait;
  use MessengerTrait;

  /**
   * Contains the configuration object factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The feed.bridge.reader service.
   *
   * @var \Drupal\aggregator\ZfExtensionManagerSfContainer
   */
  protected $reader;

  /**
   * The datetime.time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Constructs a new DefaultParser object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory object.
   * @param \Drupal\aggregator\ZfExtensionManagerSfContainer
   *   The feed.bridge.reader service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, ZfExtensionManagerSfContainer $reader, TimeInterface $time) {
    $this->configFactory = $config_factory;
    $this->reader = $reader;
    $this->time = $time;

    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
      $container->get('feed.bridge.reader'),
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['aggregator.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('aggregator.settings');
    $info = $this->getPluginDefinition();

    // Only wrap into details if there is a basic configuration.
    $form['parser'][$info['id']] = [
      '#type' => 'details',
      '#title' => $this->t('Default parser settings'),
      '#open' => TRUE,
    ];
    $form['parser'][$info['id']]['normalize_post_dates'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Normalize item post dates'),
      '#description' => $this->t("When an item is imported if that item's post date is in the future, then the date will be changed to the current timestamp. Changing this setting does not affect items which have already been imported."),
      '#default_value' => $config->get('normalize_post_dates'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->setConfiguration([
      'normalize_post_dates' => (bool) $form_state->getValue('normalize_post_dates'),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function parse(FeedInterface $feed) {
    $config = $this->config('aggregator.settings');

    // Set our bridge extension manager to Laminas Feed.
    Reader::setExtensionManager($this->reader);
    Reader::registerExtension('Aggregator');
    try {
      $channel = Reader::importString($feed->source_string);
    }
    catch (ExceptionInterface $e) {
      watchdog_exception('aggregator', $e);
      $this->messenger()->addError(new TranslatableMarkup('The feed from %url seems to be broken because of error "%error".', [
        '%url' => $feed->getUrl(),
        '%error' => $e->getMessage(),
      ]));

      return FALSE;
    }

    $feed->setWebsiteUrl($channel->getLink());
    $feed->setDescription($channel->getDescription());
    if ($image = $channel->getImage()) {
      $feed->setImage($image['uri']);
    }
    // Initialize items array.
    $feed->items = [];
    foreach ($channel as $item) {
      // Reset the parsed item.
      $parsed_item = [];
      // Move the values to an array as expected by processors.
      $parsed_item['title'] = $item->getTitle();
      $parsed_item['guid'] = $item->getId();
      $parsed_item['link'] = $item->getLink();
      $parsed_item['description'] = $item->getDescription();
      $parsed_item['author'] = '';
      if ($author = $item->getAuthor()) {
        $parsed_item['author'] = $author['name'];
      }
      elseif ($author = $item->getAggregatorAuthor()) {
        $parsed_item['author'] = $author['name'];
      }
      $parsed_item['timestamp'] = '';
      if ($date = $item->getDateModified()) {
        $parsed_item['timestamp'] = $this->parseDateModified($date, $config->get('normalize_post_dates'));
      }
      // Store on $feed object. This is where processors will look for parsed items.
      $feed->items[] = $parsed_item;
    }

    return TRUE;
  }

  /**
   * Parses item pubDate values.
   *
   * @param \DateTime $date
   *   An item pubDate.
   * @param bool|NULL $normalize
   *   If TRUE, then set any pubDate that is in the future to the current time.
   *
   * @return int
   *   A timestamp representing the item's pubDate.
   */
  protected function parseDateModified(\DateTime $date, ?bool $normalize): int {
    $item_time = $date->getTimestamp();
    $current_time = $this->time->getRequestTime();
    // If the timestamp has a future date and time, then overwrite it with
    // current date and time.
    if ($item_time > $current_time && $normalize) {
      return $current_time;
    }
    return $item_time;
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
