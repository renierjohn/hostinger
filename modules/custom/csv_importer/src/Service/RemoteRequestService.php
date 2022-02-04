<?php

namespace Drupal\csv_importer\Service;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\InvalidArgumentException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\SeekException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use GuzzleHttp\Exception\TransferException;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\State\State;
use GuzzleHttp\Client;
/**
 * Class ScheduledPublishCron
 *
 * @package Drupal\scheduled_publish\Service
 */
class RemoteRequestService {

  /**
  * The entity bundle info service.
  *
  * @var GuzzleHttp\Client
  */
  protected $client;
  /**
  * The entity bundle info service.
  *
  * @var Drupal\Core\State\State;
  */
  protected $state;
  /**
  * The entity bundle info service.
  *
  * @var Symfony\Component\HttpFoundation\RequestStack
  */
  protected $request;
  /**
   * The entity bundle info service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $config;

  /**
   * QueueManager constructor.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *
   * @param \Drupal\Core\State\State $state
   *   The time service.
   * @param \GuzzleHttp\Client $client
   *   The queue worker plugin manager service.
   * @param \Drupal\Core\Config\ConfigFactory $config
   *   The queue factory service.
   */
  public function __construct(RequestStack $request,Client $client,State $state,ConfigFactory $config){
    $this->request = $request;
    $this->client  = $client;
    $this->state   = $state;
    $this->config  = $config;
  }

  public function remoteRequestCurl($method,$uri,$params){
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL            => $uri,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING       => '',
      CURLOPT_MAXREDIRS      => 10,
      CURLOPT_TIMEOUT        => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST  => $method,
      CURLOPT_POSTFIELDS     => $params ? json_encode($params) : '',
      CURLOPT_HTTPHEADER     => array(
        'Content-Type: application/json'
      ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);  
    return json_decode($response,TRUE);
  }

}
