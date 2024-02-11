<?php

namespace Drupal\data_router\EventSubscriber;

use Drupal\Core\State\State;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Routing\CurrentRouteMatch;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Class .
 *
 * @package Drupal\
 */

class ResponseHook implements EventSubscriberInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;
  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $routeMatch;
  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\user\PrivateTempStoreFactory
   */
  protected $temp_store;
  /**
   * @var \Drupal\Core\State\State
   */
  protected $state;

  const MAX_ITEM            = 1000;

  const TEMP_PRIVATE        = 'recent_page_id';

  const MODULE              = 'datarouter';

  const COOKIE_KEY          = 'renify_t';

  const VALID_CONTENT_TYPES = ['places','routes','barkota_shipping_vessel'];

  const TRACKER_PREFIX      = 'track_';

  /**
   * Constructor.
   *
   * @param EntityTypeManager $entity_type_manager
   * @param State $state
   */
  public function __construct(EntityTypeManager $entity_type_manager,CurrentRouteMatch $routeMatch,PrivateTempStoreFactory $temp_store,State $state) {
    $this->entityTypeManager = $entity_type_manager;
    $this->routeMatch        = $routeMatch;
    $this->temp_store        = $temp_store;
    $this->state             = $state;
  }

  /**
   * {@inheritdoc}
   *
   * Get Subscribed Events
   * @return array()
   */
  static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['setCookie'];
    $events[KernelEvents::REQUEST][]  = ['getRequestHook',27];
    return $events;
  }

  public function getRequestHook(RequestEvent $event){

    $routeName  = $this->routeMatch->getRouteName();
    $request    = $event->getRequest();

    if($routeName == 'entity.node.canonical'){
      $cookies    = $request->cookies;
      if(!empty($cookies)){
        $cookies  = $cookies->all();
      }
      $node          = $request->attributes->get('node');
      $content_type  = $node->bundle();

      if(in_array($content_type,self::VALID_CONTENT_TYPES)){
        $id      = $node->id();
        $id      = $this->processCookie($cookies,$id);
        $this->temp_store->get(self::MODULE)->set(self::TEMP_PRIVATE,$id);
      }
    }

    if($routeName == 'view.cocaliong.main'){
      $cookies    = $request->cookies;
      if(!empty($cookies)){
        $cookies  = $cookies->all();
      }
      $id = $this->processCookie($cookies,'CCL');
      $this->temp_store->get(self::MODULE)->set(self::TEMP_PRIVATE,$id);
    }
  }

  public function setCookie(ResponseEvent $event){
    $routeMatch = $this->routeMatch;
    $routeName  = $routeMatch->getRouteName();
    if($routeName == 'entity.node.canonical' || $routeName ==  'view.cocaliong.main'){
      $id       = $this->temp_store->get(self::MODULE)->get(self::TEMP_PRIVATE);
      $response = $event->getResponse();
      if(!empty($id)){
        $response->headers->setCookie(new Cookie(self::COOKIE_KEY,$id));
      }
    }
  }

  private function processCookie($cookies,$nid){
      if(empty($cookies)){
        return $nid;
      }
      $recent_page_cookie = !empty($cookies[self::COOKIE_KEY]) ? $cookies[self::COOKIE_KEY] : FALSE;
      if(empty($recent_page_cookie)){
        $this->storeCount($nid);
        return $nid;
      }
      $recent_page_cookie_arr = explode(',',$recent_page_cookie);

      if(!in_array($nid,$recent_page_cookie_arr)){
        $this->storeCount($nid);
        $recent_page_cookie_arr[] = $nid;
        if(count($recent_page_cookie_arr) > self::MAX_ITEM){
          unset($recent_page_cookie_arr[0]);
        }
        return implode(',',$recent_page_cookie_arr);
      }
      return $recent_page_cookie;
  }

  private function storeCount($id){
    $state = $this->state;
    $count = $state->get(self::TRACKER_PREFIX.$id);
    if(empty($count)){
      $count = 0;
    }
    $state->set(self::TRACKER_PREFIX.$id,$count + 1);
  }

}
