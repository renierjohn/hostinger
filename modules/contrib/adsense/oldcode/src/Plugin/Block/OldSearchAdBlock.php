<?php

namespace Drupal\adsense_oldcode\Plugin\Block;

use Drupal\adsense\AdBlockInterface;
use Drupal\adsense_oldcode\Plugin\AdsenseAd\OldSearchAd;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an AdSense Custom Search ad block.
 *
 * @Block(
 *   id = "adsense_oldsearch_ad_block",
 *   admin_label = @Translation("Old search"),
 *   category = @Translation("Adsense")
 * )
 */
class OldSearchAdBlock extends BlockBase implements AdBlockInterface, ContainerFactoryPluginInterface {

  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Creates a new OldCodeAdBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'ad_channel' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function createAd() {
    return new OldSearchAd(['channel' => $this->configuration['ad_channel']]);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return $this->createAd()->display();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    // Hide block title by default.
    $form['label_display']['#default_value'] = FALSE;

    $channel_list = [];
    for ($channel = 1; $channel <= ADSENSE_OLDCODE_MAX_CHANNELS; $channel++) {
      $title = $this->configFactory->get('adsense_oldcode.settings')->get('adsense_ad_channel_' . $channel);
      if (!empty($title)) {
        $channel_list[$channel] = $title;
      }
    }

    $form['ad_channel'] = [
      '#type' => 'select',
      '#title' => $this->t('Channel'),
      '#default_value' => $this->configuration['ad_channel'],
      '#options' => $channel_list,
      '#empty_value' => '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['ad_channel'] = $form_state->getValue('ad_channel');
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(),
      $this->configFactory->get('adsense.settings')->getCacheContexts(),
      $this->configFactory->get('adsense_oldcode.settings')->getCacheContexts(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(),
      $this->configFactory->get('adsense.settings')->getCacheTags(),
      $this->configFactory->get('adsense_oldcode.settings')->getCacheTags()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return Cache::PERMANENT;
  }

}
