<?php

namespace Drupal\rest_api_access_token\EventSubscriber;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\rest_api_access_token\Authentication\Provider\AccessTokenProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CacheEndpointSubscriber.
 *
 * @package Drupal\rest_api_access_token\EventSubscriber
 */
class CacheEndpointSubscriber implements EventSubscriberInterface {

  const CACHE_KEY = 'rest_api_access_token_cache';

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Cache service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * CacheEndpointSubscriber constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   Time service.
   */
  public function __construct(ConfigFactoryInterface $configFactory, CacheBackendInterface $cache, TimeInterface $time) {
    $this->configFactory = $configFactory;
    $this->cache = $cache;
    $this->time = $time;
  }

  /**
   * Generate uniq for request and user cache key.
   *
   * @param string $requestId
   *   Request id.
   * @param string $userToken
   *   User auth token.
   * @param string $path
   *   Request URI.
   *
   * @return string
   *   Cache key.
   */
  protected function getCacheKey(string $requestId, string $userToken, string $path) {
    return self::CACHE_KEY . ':' . md5($userToken) . ':' . str_replace('/', '.', $path) . ':' . $requestId;
  }

  /**
   * Get cached request.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   Response event.
   */
  public function onKernelRequest(GetResponseEvent $event) {
    if (!$event->isMasterRequest()) {
      return;
    }

    $request = $event->getRequest();
    $requestId = (string) $request->headers->get(AccessTokenProvider::REQUEST_ID);
    $publicToken = (string) $request->headers->get(AccessTokenProvider::TOKEN);

    $config = $this->configFactory->get('rest_api_access_token.config');
    if ($config->get('cache_endpoints') && !empty($requestId)) {
      $path = $request->getPathInfo();
      $cid = $this->getCacheKey($requestId, $publicToken, $path);
      if ($cache = $this->cache->get($cid)) {
        $cacheResponse = $cache->data;
        if ($cacheResponse instanceof Response) {
          $cacheResponse->send();
          exit;
        }
      }
    }
  }

  /**
   * Store request response in cache.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   Response event.
   */
  public function onKernelResponse(FilterResponseEvent $event) {
    $response = $event->getResponse();
    $request = $event->getRequest();
    $requestId = (string) $request->headers->get(AccessTokenProvider::REQUEST_ID);
    $publicToken = (string) $request->headers->get(AccessTokenProvider::TOKEN);

    $config = $this->configFactory->get('rest_api_access_token.config');
    if ($config->get('cache_endpoints') && !empty($requestId) && $response->getContent()) {
      $path = $request->getPathInfo();
      $cid = $this->getCacheKey($requestId, $publicToken, $path);
      $currentTime = $this->time->getCurrentTime();
      $lifetime = (int) $this->configFactory->get('rest_api_access_token.config')
        ->get('cache_endpoints_lifetime');
      $expire = $currentTime + $lifetime;
      if ($lifetime <= 0) {
        $expire = Cache::PERMANENT;
      }
      $this->cache->set($cid, $response, $expire);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onKernelRequest', 900];
    $events[KernelEvents::RESPONSE][] = ['onKernelResponse', -900];
    return $events;
  }

}
