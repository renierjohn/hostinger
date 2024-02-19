<?php

namespace Drupal\adsense;

/**
 * Interface for adsense ad blocks.
 */
interface AdBlockInterface {

  /**
   * Create ad object.
   *
   * @return AdsenseAdBase
   *   The created ad.
   */
  public function createAd();

}
