<?php

namespace Drupal\rest_api_access_token\Authentication\Provider;

use Drupal\Core\Authentication\AuthenticationProviderInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\rest_api_access_token\Exception\AccessDeniedException;
use Drupal\rest_api_access_token\Exception\InvalidRequestIdException;
use Drupal\rest_api_access_token\Exception\TokenNotFoundException;
use Drupal\rest_api_access_token\Repository\TokenRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AccessTokenProvider.
 *
 * @package Drupal\rest_api_access_token\Authentication\Provider
 */
class AccessTokenProvider implements AuthenticationProviderInterface {

  const REQUEST_ID = 'REQUEST-ID';

  const TOKEN = 'X-AUTH-TOKEN';

  const SIGNATURE = 'X-AUTH-SIGNATURE';

  /**
   * Token repository.
   *
   * @var \Drupal\rest_api_access_token\Repository\TokenRepository
   */
  protected $tokenRepository;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $translator;

  /**
   * User storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * AccessTokenProvider constructor.
   *
   * @param \Drupal\rest_api_access_token\Repository\TokenRepository $tokenRepository
   *   Token repository.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translator
   *   Translator.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   Invalid plugin definition exception.
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   Plugin not found exception.
   */
  public function __construct(TokenRepository $tokenRepository, ConfigFactoryInterface $configFactory, TranslationInterface $translator, EntityTypeManagerInterface $entityTypeManager) {
    $this->tokenRepository = $tokenRepository;
    $this->configFactory = $configFactory;
    $this->translator = $translator;
    $this->userStorage = $entityTypeManager->getStorage('user');
  }

  /**
   * {@inheritdoc}
   */
  public function applies(Request $request) {
    return !(empty($request->headers->get(self::TOKEN)) && empty($request->query->get(self::TOKEN)));
  }

  /**
   * Authenticate user via tokens in request header.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Symfony http request.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   User entity.
   *
   * @throws \Exception
   *   Throws exception when authenticate failed.
   */
  public function authenticate(Request $request) {
    $publicToken = (string) $request->headers->get(self::TOKEN);
    if (empty($publicToken)) {
      $publicToken = (string) $request->query->get(self::TOKEN);
    }
    $signature = (string) $request->headers->get(self::SIGNATURE);
    if (empty($signature)) {
      $signature = (string) $request->query->get(self::SIGNATURE);
    }

    $requestId = (string) $request->headers->get(self::REQUEST_ID);
    if (empty($requestId)) {
      $requestId = (string) $request->query->get(self::REQUEST_ID);
    }

    $path = $request->getPathInfo();

    if (empty($publicToken)) {
      throw new AccessDeniedException($this->translator->translate('Empty X-AUTH-TOKEN'));
    }

    if ($this->configFactory->get('rest_api_access_token.config')
      ->get('cache_endpoints')) {
      if (empty($requestId)) {
        throw new InvalidRequestIdException($this->translator->translate('Empty REQUEST-ID'));
      }
    }

    try {
      $token = $this->tokenRepository->getByPublicToken($publicToken);
    }
    catch (TokenNotFoundException $exception) {
      throw new AccessDeniedException($this->translator->translate('Invalid X-AUTH-TOKEN'));
    }

    $user = $this->userStorage->load($token->getUserId());
    if (!$user || !$user->isActive()) {
      throw new AccessDeniedException($this->translator->translate('Invalid user account'));
    }

    if ($this->configFactory->get('rest_api_access_token.config')
      ->get('signature_verification')) {
      $value = $publicToken . '|' . $requestId . '|' . $path . '|' . base64_encode($request->getContent()) . '|' . $token->getSecret();
      if ($signature !== hash('sha256', $value)) {
        throw new AccessDeniedException($this->translator->translate('Invalid X-AUTH-SIGNATURE'));
      }
    }

    return $user;
  }

}
