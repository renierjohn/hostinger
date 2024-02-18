<?php

namespace Drupal\rest_api_access_token\PageCache\RequestPolicy;

use Drupal\Core\PageCache\RequestPolicyInterface;
use Drupal\rest_api_access_token\Authentication\Provider\AccessTokenProvider;
use Symfony\Component\HttpFoundation\Request;

/**
 * Do not serve a page from cache if X-AUTH-TOKEN token is applicable.
 *
 * @internal
 */
class DisallowXAuthTokenRequests implements RequestPolicyInterface {

  /**
   * {@inheritdoc}
   */
  public function check(Request $request) {
    if (!(empty($request->headers->get(AccessTokenProvider::TOKEN)) && empty($request->query->get(AccessTokenProvider::TOKEN)))) {
      return self::DENY;
    }
  }

}
