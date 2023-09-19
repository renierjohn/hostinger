<?php

namespace Drupal\rest_api_access_token\Model;

/**
 * Token model.
 *
 * @package Drupal\rest_api_access_token\Model
 */
class Token {

  const TABLE_NAME = 'rest_api_access_token';

  /**
   * Public key.
   *
   * @var string
   */
  protected $public;

  /**
   * Private key.
   *
   * @var string
   */
  protected $secret;

  /**
   * User ID.
   *
   * @var int
   */
  protected $userId;

  /**
   * @var \DateTime
   */
  private $createdAt;

  /**
   * @var \DateTime
   */
  private $refreshedAt;

  /**
   * Token constructor.
   *
   * @param string $public
   *   Public key.
   * @param string $secret
   *   Private key.
   * @param int $userId
   *   User id.
   */
  public function __construct(string $public, string $secret, int $userId, \DateTime $createdAt = NULL, \DateTime $refreshedAt = NULL) {
    $this->public = $public;
    $this->secret = $secret;
    $this->userId = $userId;
    $this->createdAt = $createdAt ?? new \DateTime();
    $this->refreshedAt = $refreshedAt ?? new \DateTime();
  }

  /**
   * Get public key.
   *
   * @return string
   *   Return public key.
   */
  public function getPublic(): string {
    return $this->public;
  }

  /**
   * Get private key.
   *
   * @return string
   *   Return private key.
   */
  public function getSecret(): string {
    return $this->secret;
  }

  /**
   * Get user id.
   *
   * @return int
   *   Return user id.
   */
  public function getUserId(): int {
    return $this->userId;
  }

  /**
   * @return \DateTime
   */
  public function getCreatedAt(): \DateTime {
    return $this->createdAt;
  }

  /**
   * @return \DateTime
   */
  public function getRefreshedAt(): \DateTime {
    return $this->refreshedAt;
  }

}
