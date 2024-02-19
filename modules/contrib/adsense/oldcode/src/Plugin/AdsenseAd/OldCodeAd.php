<?php

namespace Drupal\adsense_oldcode\Plugin\AdsenseAd;

use Drupal\adsense\ContentAdBase;
use Drupal\adsense\PublisherId;

/**
 * Provides an AdSense old code ad unit.
 *
 * @AdsenseAd(
 *   id = "oldcode",
 *   name = @Translation("Old code ads"),
 *   isSearch = FALSE,
 *   needsSlot = FALSE
 * )
 */
class OldCodeAd extends ContentAdBase {

  /**
   * Ad style (key to configured styles).
   *
   * @var int
   */
  private $style;

  /**
   * Ad Channel.
   *
   * @var string
   */
  private $channel;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id = '', $plugin_definition = NULL, $config_factory = NULL, $module_handler = NULL, $current_user = NULL) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $config_factory, $module_handler, $current_user);
    $fo = (!empty($configuration['format'])) ? $configuration['format'] : '';
    $st = (!empty($configuration['style'])) ? $configuration['style'] : 1;
    $ch = (!empty($configuration['channel'])) ? $configuration['channel'] : '';

    if (($st < 1) || ($st > ADSENSE_OLDCODE_MAX_GROUPS)) {
      // Default to 1 if an invalid style is supplied.
      $st = 1;
    }

    if ((substr($fo, 0, 10) != 'Search Box') && !empty($fo)) {
      $this->format = $fo;
      $this->style = $st;
      $this->channel = $ch;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getAdPlaceholder() {
    if (!empty($this->format)) {
      $client = PublisherId::get();
      // Get width and height from the format.
      [$width, $height] = $this->dimensions($this->format);

      $content = $this->configFactory->get('adsense.settings')->get('adsense_placeholder_text');
      $content .= "\nclient = $client\nformat = {$this->format}\nwidth = $width\nheight = $height\nstyle = {$this->style}\nchannel = {$this->channel}";

      return [
        '#content' => ['#markup' => nl2br($content)],
        '#format' => $this->format,
        '#width' => $width,
        '#height' => $height,
      ];
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getAdContent() {
    if (!empty($this->format)) {
      $ad = $this->adsenseAdFormats($this->format);
      if ($ad != NULL) {
        $core_config = $this->configFactory->get('adsense.settings');
        $oldcode_config = $this->configFactory->get('adsense_oldcode.settings');

        $client = PublisherId::get();
        $this->moduleHandler->alter('adsense', $client);

        // Get width and height from the format.
        [$width, $height] = $this->dimensions($this->format);

        switch ($oldcode_config->get('adsense_ad_type_' . $this->style)) {
          case 0:
            $type = 'text';
            break;

          case 1:
            $type = 'image';
            break;

          default:
            $type = 'text_image';
            break;
        }

        $alt = $oldcode_config->get('adsense_alt_' . $this->style);
        $alt_info = $oldcode_config->get('adsense_alt_info_' . $this->style);

        return [
          '#theme' => 'adsense_oldcode',
          '#client' => $client,
          '#alt_url' => ($alt == 1) ? $alt_info : '',
          '#alt_color' => ($alt == 2) ? $alt_info : '',
          '#width' => $width,
          '#height' => $height,
          '#format' => $ad['code'],
          '#type' => ($ad['type'] == ADSENSE_OLDCODE_TYPE_AD) ? $type : '',
          '#channel' => $oldcode_config->get('adsense_ad_channel_' . $this->channel),
          '#border' => mb_substr($oldcode_config->get('adsense_color_border_' . $this->style), 1),
          '#bg' => mb_substr($oldcode_config->get('adsense_color_bg_' . $this->style), 1),
          '#link' => mb_substr($oldcode_config->get('adsense_color_link_' . $this->style), 1),
          '#text' => mb_substr($oldcode_config->get('adsense_color_text_' . $this->style), 1),
          '#url' => mb_substr($oldcode_config->get('adsense_color_url_' . $this->style), 1),
          '#features' => $oldcode_config->get('adsense_ui_features_' . $this->style),
          '#secret' => $core_config->get('adsense_secret_language'),
        ];
      }
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public static function adsenseAdFormats($key = NULL) {
    $ads = [
      // Top performing ad sizes.
      '300x250' => [
        'type' => ADSENSE_OLDCODE_TYPE_AD,
        'desc' => t('Medium Rectangle'),
        'code' => '300x250_as',
      ],
      '336x280' => [
        'type' => ADSENSE_OLDCODE_TYPE_AD,
        'desc' => t('Large Rectangle'),
        'code' => '336x280_as',
      ],
      '728x90' => [
        'type' => ADSENSE_OLDCODE_TYPE_AD,
        'desc' => t('Leaderboard'),
        'code' => '728x90_as',
      ],
      // Other supported ad sizes.
      '468x60' => [
        'type' => ADSENSE_OLDCODE_TYPE_AD,
        'desc' => t('Banner'),
        'code' => '468x60_as',
      ],
      '234x60' => [
        'type' => ADSENSE_OLDCODE_TYPE_AD,
        'desc' => t('Half Banner'),
        'code' => '234x60_as',
      ],
      '120x600' => [
        'type' => ADSENSE_OLDCODE_TYPE_AD,
        'desc' => t('Skyscraper'),
        'code' => '120x600_as',
      ],
      '120x240' => [
        'type' => ADSENSE_OLDCODE_TYPE_AD,
        'desc' => t('Vertical Banner'),
        'code' => '120x240_as',
      ],
      '160x600' => [
        'type' => ADSENSE_OLDCODE_TYPE_AD,
        'desc' => t('Wide Skyscraper'),
        'code' => '160x600_as',
      ],
      '250x250' => [
        'type' => ADSENSE_OLDCODE_TYPE_AD,
        'desc' => t('Square'),
        'code' => '250x250_as',
      ],
      '200x200' => [
        'type' => ADSENSE_OLDCODE_TYPE_AD,
        'desc' => t('Small Square'),
        'code' => '200x200_as',
      ],
      '180x150' => [
        'type' => ADSENSE_OLDCODE_TYPE_AD,
        'desc' => t('Small Rectangle'),
        'code' => '180x150_as',
      ],
      '125x125' => [
        'type' => ADSENSE_OLDCODE_TYPE_AD,
        'desc' => t('Button'),
        'code' => '125x125_as',
      ],
    ];

    if (!empty($key)) {
      return (array_key_exists($key, $ads)) ? $ads[$key] : NULL;
    }
    else {
      return $ads;
    }
  }

}
