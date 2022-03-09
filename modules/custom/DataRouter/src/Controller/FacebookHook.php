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
class FacebookHook extends ControllerBase {

  protected $state;

  protected $request;

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
    return new JsonResponse($request);
  }

  private function log($message){
    $log  = fopen('private://fb_page_hook.txt','a');
    $date = date("l jS \of F Y h:i:s A"); 
    fwrite($log,$date.' '.$message.PHP_EOL);
    fclose($log);
  }

}
