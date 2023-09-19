<?php

namespace Drupal\rest_api_access_token\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\rest_api_access_token\Exception\AuthenticationException;
use Drupal\rest_api_access_token\Exception\TokenGeneratorException;
use Drupal\rest_api_access_token\Exception\TokenNotFoundException;
use Drupal\rest_api_access_token\Repository\TokenRepository;
use Drupal\user\Entity\User;
use Drupal\user\UserAuthInterface;

/**
 * Class LoginService.
 *
 * @package Drupal\rest_api_access_token\Service
 */
class LoginService {

  /**
   * Token generator service.
   *
   * @var \Drupal\rest_api_access_token\Service\TokenGenerator
   */
  protected $tokenGenerator;

  /**
   * Token model repository.
   *
   * @var \Drupal\rest_api_access_token\Repository\TokenRepository
   */
  protected $tokenRepository;

  /**
   * Entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * User authentication service.
   *
   * @var \Drupal\user\UserAuthInterface
   */
  protected $userAuth;

  /**
   * LoginService constructor.
   *
   * @param \Drupal\rest_api_access_token\Service\TokenGenerator $tokenGenerator
   *   Token generator service.
   * @param \Drupal\rest_api_access_token\Repository\TokenRepository $tokenRepository
   *   Token model repository.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity manager service.
   * @param \Drupal\user\UserAuthInterface $userAuth
   *   User authentication service.
   */
  public function __construct(TokenGenerator $tokenGenerator, TokenRepository $tokenRepository, EntityTypeManagerInterface $entityTypeManager, UserAuthInterface $userAuth) {
    $this->tokenGenerator = $tokenGenerator;
    $this->tokenRepository = $tokenRepository;
    $this->entityTypeManager = $entityTypeManager;
    $this->userAuth = $userAuth;
  }

  /**
   * User login.
   *
   * @param string $login
   *   User email or username.
   * @param string $password
   *   User password.
   *
   * @return bool|\Drupal\rest_api_access_token\Model\Token
   *   Token model
   *
   * @throws \Exception
   *   Invalid user login or password.
   */
  public function login(string $login, string $password) {
    if (empty($login) || empty($password)) {
      throw new AuthenticationException("Invalid user login or password");
    }

    // Login by email.
    $usersByEmail = $this->entityTypeManager->getStorage('user')
      ->loadByProperties(['mail' => $login]);
    $userByEmail = reset($usersByEmail);

    if ($userByEmail) {
      $login = $userByEmail->getAccountName();
    }

    // Authenticate user.
    $uid = (int) $this->userAuth->authenticate($login, $password);
    if ($uid <= 0) {
      throw new AuthenticationException("Invalid user login or password");
    }

    try {
      for ($i = 0; $i < 5; $i++) {
        $token = $this->tokenGenerator->execute($uid);
        $this->tokenRepository->getByPublicToken($token->getPublic());
        $token = FALSE;
      }
    }
    catch (TokenNotFoundException $exception) {
    }

    if (empty($token)) {
      throw new TokenGeneratorException("Error during generate token");
    }

    $this->tokenRepository->insert($token);

    return $token;
  }

  /**
   * Logout user form current device.
   *
   * @param string $publicToken
   *   Public user auth token.
   *
   * @return bool
   *   Logout result.
   */
  public function logout(string $publicToken) {
    return (bool) $this->tokenRepository->removeByPublicToken($publicToken);
  }

  /**
   * Logout user form all devices.
   *
   * @param \Drupal\user\Entity\User $user
   *   User entity.
   *
   * @return bool
   *   Logout result.
   */
  public function logoutFromAllDevices(User $user) {
    return (bool) $this->tokenRepository->removeByUser($user);
  }

}
