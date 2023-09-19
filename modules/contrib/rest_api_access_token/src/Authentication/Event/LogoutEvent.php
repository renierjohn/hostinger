<?php

namespace Drupal\rest_api_access_token\Authentication\Event;

use Drupal\user\Entity\User;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LogoutEvent extends Event.
 *
 * @package Drupal\rest_api_access_token\Authentication\Event
 */
class LogoutEvent extends Event {

  /**
   * Request model.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  private $request;

  /**
   * User entity.
   *
   * @var \Drupal\user\Entity\User
   */
  private $user;

  /**
   * LogoutEvent constructor.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request model.
   * @param \Drupal\user\Entity\User $user
   *   User entity.
   */
  public function __construct(Request $request, User $user) {
    $this->request = $request;
    $this->user = $user;
  }

  /**
   * Get Request model.
   *
   * @return \Symfony\Component\HttpFoundation\Request
   *   Request model.
   */
  public function getRequest(): Request {
    return $this->request;
  }

  /**
   * Get User entity.
   *
   * @return \Drupal\user\Entity\User
   *   User entity.
   */
  public function getUser(): User {
    return $this->user;
  }

}
