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
use Drupal\data_router\Service\BookingService;

class BookingController extends ControllerBase {

  protected $state;

  protected $request;

  protected $bookingTemplate;

  public function __construct(State $state,RequestStack $request,BookingService $bookingTemplate) {
    $this->state   = $state;
    $this->request = $request->getCurrentRequest();
    $this->bookingTemplate = $bookingTemplate;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('state'),
      $container->get('request_stack'),
      $container->get('data_router.bookservice')
    );
  }

  public function render($hash){
    $bookingTemplate = $this->bookingTemplate->sethash($hash)->getBookingTemplate();
    if(empty($bookingTemplate)){
      return new RedirectResponse('/404');
    }
    return new HtmlResponse($bookingTemplate['template']);
  }

}
