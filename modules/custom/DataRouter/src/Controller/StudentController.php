<?php

namespace Drupal\data_router\Controller;

use Drupal\Core\State\State;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

use Drupal\data_router\Service\StudentService;

class StudentController extends ControllerBase {

  protected $state;

  protected $request;

  protected $student;

  public function __construct(State $state,RequestStack $request,StudentService $student) {
    $this->state   = $state;
    $this->student = $student;
    $this->request = $request->getCurrentRequest();
  }
  
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('state'),
      $container->get('request_stack'),
      $container->get('data_router.student')
    );
  }

  /**
   * Browser Page.
   *
   * @return string
   *   Return Hello string.
   */
  public function getData() {
    $request    = $this->request->query->all();
    $qr_hash    = $request['qr'] ? $request['qr'] : [];
    $student    = $this->student->sethash($qr_hash)->getData();
    if(empty($student)){
      return new JsonResponse(['status'=>FALSE]);  
    }
    return new JsonResponse(['status'=>TRUE,'data'=>$student]);
  }

  public function page(){
    $build = [
      '#theme' => 'student',
      '#attached' => [
        'library' => [
          'data_router/qr'
        ]
      ],
    ];
    return $build;
  }

}
