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

  const LIMIT = 9;

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

  public function renderStudentScanner(){
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

  public function renderStudentList(){
    $request = $this->request->query->all();
    $gender  = !empty($request['g'])    ? $request['g']   : '';
    $level   = !empty($request['lvl']) ? $request['lvl'] : '';
    $present = !empty($request['p'])   ? $request['p'] : False;

    $students  = $this->student->query(self::LIMIT,0,$gender,$level,$present);
    $build = [
      '#theme' => 'student_list',
      '#attached' => [
        'library' => [
          'data_router/student'
        ]
      ],
      '#data' => [
        'start'     => 0,
        'limit'     => self::LIMIT,
        'level'     => $this->student->getLevels(),
        'more_flag' => count($students) > self::LIMIT ? True : False, 
        'students'  => count($students) > self::LIMIT ? array_splice($students,0,self::LIMIT) : $students,
      ],
    ];
    return $build;
  }

  public function ajaxStudentList(){
    $request = $this->request->query->all();
    $limit   = !empty($request['l'])    ? $request['l']   : 10;
    $start   = !empty($request['s'])    ? $request['s']   : 0;
    $gender  = !empty($request['g'])    ? $request['g']   : '';
    $level   = !empty($request['lvl']) ? $request['lvl'] : '';
    $present = !empty($request['p'])   ? $request['p'] : False;

    $students  = $this->student->query($limit,$start,$gender,$level,$present);
    $more_flag = count($students) > $limit ? True : False;
    $students  = array_splice($students,0,$limit);
    $data = [
      'students'  => $students,
      'more_flag' => $more_flag,
    ];
    return new JsonResponse($data);
  }

}
