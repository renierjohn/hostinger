<?php

namespace Drupal\rest_api_access_token\Authentication\Event;

use Drupal\rest_api_access_token\Model\Token;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class TokenResponseEvent extends Event.
 *
 * @package Drupal\rest_api_access_token\Authentication\Event
 */
class TokenResponseEvent extends Event {

  /**
   * Token model.
   *
   * @var \Drupal\rest_api_access_token\Model\Token
   */
  private $token;

  /**
   * Request content.
   *
   * @var array
   */
  private $requestContent = [];

  /**
   * Access flag.
   *
   * @var bool
   */
  private $hasAccess = TRUE;

  /**
   * Error message.
   *
   * @var string
   */
  private $errorMessage = '';

  /**
   * TokenResponseEvent constructor.
   *
   * @param \Drupal\rest_api_access_token\Model\Token $token
   *   Token model.
   * @param array $requestContent
   *   Request content.
   */
  public function __construct(Token $token, array $requestContent) {
    $this->token = $token;
    $this->requestContent = $requestContent;
  }

  /**
   * Get Token model.
   *
   * @return \Drupal\rest_api_access_token\Model\Token
   *   Token model.
   */
  public function getToken(): Token {
    return $this->token;
  }

  /**
   * Get request content.
   *
   * @return array
   *   Request content.
   */
  public function getRequestContent(): array {
    return $this->requestContent;
  }

  /**
   * Get error message.
   *
   * @return string
   *   Error message.
   */
  public function getErrorMessage(): string {
    return $this->errorMessage;
  }

  /**
   * Set access flag.
   *
   * @param bool $hasAccess
   *   Access flag.
   */
  public function setHasAccess(bool $hasAccess) {
    $this->hasAccess = $hasAccess;
  }

  /**
   * Set error message.
   *
   * @param string $errorMessage
   *   Error message.
   */
  public function setErrorMessage(string $errorMessage) {
    $this->errorMessage = $errorMessage;
  }

  /**
   * Return access flag.
   *
   * @return bool
   *   Access flag.
   */
  public function hasAccess(): bool {
    return $this->hasAccess;
  }

}
