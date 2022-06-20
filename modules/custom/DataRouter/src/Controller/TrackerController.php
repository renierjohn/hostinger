<?php

namespace Drupal\data_router\Controller;

use Drupal\Core\State\State;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
/**
 * Class ImceAdminBrowserController.
 */
class TrackerController extends ControllerBase {

  protected $state;

  protected $request;

  const QUERY_PARAM         = 't';

  const TRACKER_PREFIX      = 'track_';

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
  public function getViewCount() {
    $request    = $this->request->query->all();
    $view_count = $request[self::QUERY_PARAM];
    $summary    = $this->getViewsSummary($view_count);
    $jsonResponse = new JsonResponse($summary);
    $jsonResponse->setCache(['etag'=>'views_tag']);
    return $jsonResponse;
  }

  private function getViewsSummary($view_count){
    if(empty($view_count)){
      return [];
    }
    $temp  = [];
    $state = $this->state;
    $view_counts = explode(',',$view_count);
    foreach ($view_counts as $view_count) {
      $count = $state->get(self::TRACKER_PREFIX.$view_count);
      if(empty($count)){
        $count = '0';
      }
      $temp[$view_count] = $count;
    }
    return $temp;
  }

}
