<?php

namespace Drupal\rest_api_access_token\Service;

use Drupal\rest_api_access_token\Exception\TokenGeneratorException;
use Drupal\rest_api_access_token\Model\Token;

/**
 * TokenGenerator for unique tokens.
 *
 * @package Drupal\rest_api_access_token\Service
 */
class TokenGenerator {

  /**
   * Generate Token model with unique hashes.
   *
   * @param int $userId
   *   User id.
   *
   * @return \Drupal\rest_api_access_token\Model\Token
   *   Token model.
   *
   * @throws \Drupal\rest_api_access_token\Exception\TokenGeneratorException
   *   When hash algorithm generate empty string.
   */
  public function execute(int $userId) {
    try {
      $publicToken = hash('sha256', bin2hex(random_bytes(64)));
      $secretToken = hash('sha256', bin2hex(random_bytes(32)));
    }
    catch (\Throwable $exception) {
      throw new TokenGeneratorException();
    }

    if (empty($publicToken) || empty($secretToken)) {
      throw new TokenGeneratorException();
    }
    return new Token($publicToken, $secretToken, $userId);
  }

}
