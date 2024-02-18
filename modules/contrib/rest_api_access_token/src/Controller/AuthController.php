<?php

namespace Drupal\rest_api_access_token\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest_api_access_token\Authentication\Event\AccessTokenEvents;
use Drupal\rest_api_access_token\Authentication\Event\LogoutEvent;
use Drupal\rest_api_access_token\Authentication\Event\TokenResponseEvent;
use Drupal\rest_api_access_token\Authentication\Provider\AccessTokenProvider;
use Drupal\rest_api_access_token\Exception\AuthenticationException;
use Drupal\rest_api_access_token\Service\LoginService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AuthController.
 *
 * @package Drupal\rest_api_access_token\Controller
 */
class AuthController extends ControllerBase {

  const FIELD_TOKEN = 'token';

  const FIELD_SECRET = 'secret';

  const FIELD_USER_ID = 'userId';

  const FIELD_LOGOUT = 'loggedOut';

  const INPUT_LOGIN = 'login';

  const INPUT_PASSWORD = 'password';

  /**
   * Access token provider.
   *
   * @var \Drupal\rest_api_access_token\Authentication\Provider\AccessTokenProvider
   */
  protected $accessTokenProvider;

  /**
   * Login service.
   *
   * @var \Drupal\rest_api_access_token\Service\LoginService
   */
  protected $loginService;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * AuthController constructor.
   *
   * @param \Drupal\rest_api_access_token\Authentication\Provider\AccessTokenProvider $accessTokenProvider
   *   Access token provider.
   * @param \Drupal\rest_api_access_token\Service\LoginService $loginService
   *   Login service.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   Current user.
   */
  public function __construct(AccessTokenProvider $accessTokenProvider, LoginService $loginService, EventDispatcherInterface $eventDispatcher, AccountProxyInterface $account) {
    $this->accessTokenProvider = $accessTokenProvider;
    $this->loginService = $loginService;
    $this->eventDispatcher = $eventDispatcher;
    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $controller = new static(
      $container->get('rest_api_access_token.authentication.access_token_provider'),
      $container->get('rest_api_access_token.authentication.login'),
      $container->get('event_dispatcher'),
      $container->get('current_user')
    );

    return $controller;
  }

  /**
   * Get token and secret after success login.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Symfony http request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   *
   * @throws \Exception
   */
  public function tokenResponse(Request $request) {
    $data = json_decode($request->getContent(), TRUE);

    $login = (string) ($data[self::INPUT_LOGIN] ?? '');
    $password = (string) ($data[self::INPUT_PASSWORD] ?? '');

    $token = $this->loginService->login($login, $password);

    $event = new TokenResponseEvent($token, $data);
    $this->eventDispatcher->dispatch(AccessTokenEvents::TOKEN_RESPONSE, $event);

    if (!$event->hasAccess()) {
      throw new AuthenticationException($event->getErrorMessage());
    }

    $response = [
      self::FIELD_TOKEN => $token->getPublic(),
      self::FIELD_SECRET => $token->getSecret(),
      self::FIELD_USER_ID => $token->getUserId(),
    ];

    return new JsonResponse($response, JsonResponse::HTTP_OK);
  }

  /**
   * Logout user from current device.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Symfony http request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function logout(Request $request) {
    $account = $this->account;
    $user = $account->getAccount();

    $publicToken = (string) $request->headers->get(AccessTokenProvider::TOKEN);
    $status = $this->loginService->logout($publicToken);

    $event = new LogoutEvent($request, $user);
    $this->eventDispatcher->dispatch(AccessTokenEvents::LOGOUT, $event);

    $response = [
      self::FIELD_LOGOUT => $status,
    ];
    return new JsonResponse($response, JsonResponse::HTTP_OK);
  }

  /**
   * Logout user from all devices.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Symfony http request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function logoutFromAllDevices(Request $request) {

    $account = $this->account;
    $user = $account->getAccount();
    $status = $this->loginService->logoutFromAllDevices($user);

    $event = new LogoutEvent($request, $user);
    $this->eventDispatcher->dispatch(AccessTokenEvents::LOGOUT, $event);

    $response = [
      self::FIELD_LOGOUT => $status,
    ];
    return new JsonResponse($response, JsonResponse::HTTP_OK);
  }

}
