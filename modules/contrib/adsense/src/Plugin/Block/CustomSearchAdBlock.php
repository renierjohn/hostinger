<?php

namespace Drupal\adsense\Plugin\Block;

use Drupal\adsense\AdBlockInterface;
use Drupal\adsense\Plugin\AdsenseAd\CustomSearchAd;
use Drupal\adsense\Plugin\AdsenseAd\CustomSearchV2Ad;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an AdSense Custom Search ad block.
 *
 * @Block(
 *   id = "adsense_cse_ad_block",
 *   admin_label = @Translation("Custom search"),
 *   category = @Translation("Adsense")
 * )
 */
class CustomSearchAdBlock extends BlockBase implements AdBlockInterface, ContainerFactoryPluginInterface {

  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Creates a CustomSearchAdBlock instance.
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
      'ad_slot' => '',
      'version' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function createAd() {
    $configuration = ['slot' => $this->configuration['ad_slot']];

    switch ($this->configuration['version']) {
      case '1':
        return new CustomSearchAd($configuration);

      case '2':
      default:
        return new CustomSearchV2Ad($configuration);
    }
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

    $link = Link::fromTextAndUrl($this->t('Google AdSense account page'), Url::fromUri('https://www.google.com/adsense/app#main/myads-springboard'))->toString();

    $form['ad_slot'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Ad ID'),
      '#default_value' => $this->configuration['ad_slot'],
      '#description' => $this->t('This is the Ad ID from your @adsensepage, such as 1234567890.',
        ['@adsensepage' => $link]),
      '#required' => TRUE,
    ];

    $default = $this->configuration['version'];
    if (empty($this->configuration['version'])) {
      // If the block has already been saved, but the version is not set, that
      // means it's a version 1, otherwise set to the latest version (2).
      $default = empty($this->configuration['ad_slot']) ? '2' : '1';
    }

    $form['version'] = [
      '#type' => 'radios',
      '#title' => $this->t('CSE Version'),
      '#default_value' => $default,
      '#options' => [
        '1' => $this->t('Version 1'),
        '2' => $this->t('Version 2'),
      ],
      '#description' => $this->t('CSE version. If unsure, choose %default.', ['%default' => 'Version 2']),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['ad_slot'] = $form_state->getValue('ad_slot');
    $this->configuration['version'] = $form_state->getValue('version');
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(),
      $this->configFactory->get('adsense.settings')->getCacheContexts()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(),
      $this->configFactory->get('adsense.settings')->getCacheTags()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return Cache::PERMANENT;
  }

}
