<?php

namespace Drupal\rest_api_access_token\PageCache\RequestPolicy;

use Drupal\Core\PageCache\RequestPolicyInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Do not serve a page from cache if X-AUTH-TOKEN token is applicable.
 *
 * @internal
 */
class DisallowXAuthTokenRequests implements RequestPolicyInterface {

  const TOKEN = 'X-AUTH-TOKEN';

  /**
   * {@inheritdoc}
   */
  public function check(Request $request) {
    if (!(empty($request->headers->get(self::TOKEN)) && empty($request->query->get(self::TOKEN)))) {
      return self::DENY;
    }
  }

}