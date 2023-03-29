<?php

namespace Drupal\rest_api_access_token\Authentication\Event;

/**
 * Class AccessTokenEvents with event names.
 *
 * @package Drupal\rest_api_access_token\Authentication\Event
 */
final class AccessTokenEvents {

  const TOKEN_RESPONSE = 'rest_api_access_token.token_response';
  const LOGOUT = 'rest_api_access_token.logout';

}
