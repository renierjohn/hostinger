<?php

namespace Drupal\aggregator\Plugin\aggregator\fetcher;

use Drupal\aggregator\Plugin\FetcherInterface;
use Drupal\aggregator\FeedInterface;
use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Core\Http\ClientFactory;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a default fetcher implementation.
 *
 * Uses the http_client service to download the feed.
 *
 * @AggregatorFetcher(
 *   id = "aggregator",
 *   title = @Translation("Default fetcher"),
 *   description = @Translation("Downloads data from a URL using Drupal's HTTP request handler.")
 * )
 */
class DefaultFetcher implements FetcherInterface, ContainerFactoryPluginInterface {

  /**
   * The HTTP client to fetch the feed data with.
   *
   * @var \Drupal\Core\Http\ClientFactory
   */
  protected $httpClientFactory;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a DefaultFetcher object.
   *
   * @param \Drupal\Core\Http\ClientFactory $http_client_factory
   *   A Guzzle client object.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   */
  public function __construct(ClientFactory $http_client_factory, LoggerInterface $logger, MessengerInterface $messenger) {
    $this->httpClientFactory = $http_client_factory;
    $this->logger = $logger;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('http_client_factory'),
      $container->get('logger.factory')->get('aggregator'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function fetch(FeedInterface $feed) {
    $request = new Request('GET', $feed->getUrl());
    $feed->source_string = '';

    // Generate conditional GET headers.
    if ($feed->getEtag()) {
      $request = $request->withAddedHeader('If-None-Match', $feed->getEtag());
    }
    if ($feed->getLastModified()) {
      $request = $request->withAddedHeader('If-Modified-Since', gmdate(DateTimePlus::RFC7231, $feed->getLastModified()));
    }

    try {
      $response = $this->httpClientFactory->fromOptions([
        'allow_redirects' => [
          'on_redirect' => function (RequestInterface $request, ResponseInterface $response, UriInterface $uri) use ($feed) {
            $actual_uri = (string) $uri;
            // Update the feed URL in case of a 301 or 308 (permanent) redirect.
            // Do not update the URL for temporary redirects (302, 307, etc).
            if (in_array($response->getStatusCode(), [301, 308]) && $actual_uri !== $feed->getUrl()) {
              $feed->setUrl($actual_uri);
            }
          },
        ],
      ])->send($request);

      // In case of a 304 Not Modified, there is no new content, so return
      // FALSE.
      if ($response->getStatusCode() == 304) {
        return FALSE;
      }

      $feed->source_string = (string) $response->getBody();
      if ($response->hasHeader('ETag')) {
        $feed->setEtag($response->getHeaderLine('ETag'));
      }
      if ($response->hasHeader('Last-Modified')) {
        $feed->setLastModified(strtotime($response->getHeaderLine('Last-Modified')));
      }
      return TRUE;
    }
    catch (TransferException $e) {
      $this->logger->warning('The feed from %url seems to be broken because of error "%error".', [
        '%url' => $feed->getUrl(),
        '%error' => $e->getMessage(),
      ]);
      $this->messenger->addWarning(new TranslatableMarkup('The feed from %url seems to be broken because of error "%error".', [
        '%url' => $feed->getUrl(),
        '%error' => $e->getMessage(),
      ]));
      return FALSE;
    }
  }

}
