<?php

namespace Drupal\adsense\Plugin\AdsenseAd;

use Drupal\adsense\SearchAdBase;
use Drupal\adsense\PublisherId;

/**
 * Provides an AdSense custom search engine form.
 *
 * @AdsenseAd(
 *   id = "csev2",
 *   name = @Translation("CSE V2 Search"),
 *   isSearch = TRUE,
 *   needsSlot = TRUE,
 *   version = 2
 * )
 */
class CustomSearchV2Ad extends SearchAdBase {

  /**
   * Ad slot ID.
   *
   * @var string
   */
  private $slot;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id = '', $plugin_definition = NULL, $config_factory = NULL, $module_handler = NULL, $current_user = NULL) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $config_factory, $module_handler, $current_user);
    $this->slot = (!empty($configuration['slot'])) ? $configuration['slot'] : '';
  }

  /**
   * {@inheritdoc}
   */
  public function getAdPlaceholder() {
    if (!empty($this->slot)) {
      $client = PublisherId::get();

      $content = "CSE v2\ncx = partner-$client:{$this->slot}";

      return [
        '#content' => ['#markup' => nl2br($content)],
        '#format' => 'Search Box v2',
      ];
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getAdContent() {
    if (!empty($this->slot)) {
      $client = PublisherId::get();
      $this->moduleHandler->alter('adsense', $client);

      return [
        '#theme' => 'adsense_cse_v2_searchbox',
        '#client' => $client,
        '#slot' => $this->slot,
      ];
    }
    return [];
  }

}
