<?php

namespace Drupal\data_router\Controller;

use Drupal\Core\State\State;
use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
/**
 * Class ImceAdminBrowserController.
 */
class FacebookHook extends ControllerBase {

  protected $state;

  protected $request;

  const BASE_FB_PATH = 'https://graph.facebook.com';

  public function __construct(State $state,RequestStack $request) {
    $this->state   = $state;
    $this->request = $request->getCurrentRequest();
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('state'),
      $container->get('request_stack')
    );
  }

  /**
   * Browser Page.
   *
   * @return string
   *   Return Hello string.
   */
  public function page_hook() {
    $request    = $this->request->query->all();
    $this->log(json_encode($request));
    $hub_mode         = !empty($request['hub_mode']) ? $request['hub_mode'] : ''; 
    $hub_verify_token = !empty($request['hub_verify_token']) ? $request['hub_verify_token'] : '';
    $hub_challenge    = !empty($request['hub_challenge']) ? $request['hub_challenge'] : '';
    // return new JsonResponse([$hub_challenge]);
    Cache::invalidateTags(['route_match','rendered']);
    return new HtmlResponse($hub_challenge);
  }

  private function log($message){
    $log  = fopen('private://fb_page_hook.txt','a');
    $date = date("l jS \of F Y h:i:s A"); 
    fwrite($log,$date.' '.$message.PHP_EOL);
    fclose($log);
  }

  public function getFeeds(){
    // $oath =  \Drupal::service('csv_importer.remote')->remoteRequestCurl('GET',self::BASE_FB_PATH.'/oauth/access_token?client_id='.$client_id.'&client_secret='.$client_secret.'&grant_type=client_credentials');
    // 'https://graph.facebook.com/oauth/access_token?client_id=376061863589278&client_secret=f8a808ee395dcb432a9c89f5ddcd8a8f&grant_type=client_credentials' //get access token
    // 'https://graph.facebook.com/3724697254215855/accounts?access_token=376061863589278|BfELsmL_U9xNzTnXc7l7K41DOFs' // get page access token
    // 'https://graph.facebook.com/v12.0/renify.official/feed?fields=id%2Cfull_picture%2Cmessage&access_token=EAAFWBrnK9ZA4BAOc4zNb2Ry6Owz3MLGTwf53VMLt5kW7dzCax9ZCYv5ZBnZCtYLKvWL6SVPxO8z2fZArvrx1yYZBiZCCOTr3ZBoUa2ImIFigbI5B6liPbHYuuNn27sbPh6QoO3ZAYU2Wr7l9sjzf3WbJvINBVH7IZCLPWNxsYQWPWkmXja73om3Q6a2xwpw2Hp3wZC5L8EtlDF1QF1J1fR9sUnY5ZBJhjTkIhl8ZD' // get feeds page
  }

}
