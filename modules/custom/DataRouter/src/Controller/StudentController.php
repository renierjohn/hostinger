<?php

namespace Drupal\data_router\Controller;

use Drupal\Core\State\State;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

use Drupal\data_router\Service\StudentService;

class StudentController extends ControllerBase {

  protected $state;

  protected $request;

  protected $student;

  const LIMIT     = 7;

  const LIMIT_ALL = 9;

  const PATH      = 'private://students';

  const PATH_FLAG = 'public://students_flag'; 

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

  public function ajaxRecent(){
    $hash = $this->student->getLatestHash();
    return new JsonResponse($hash);
  }

  public function ajaxStudentList(){
    $request = $this->request->query->all();
    $limit   = !empty($request['l'])    ? $request['l']   : self::LIMIT_ALL;
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
      'limit'     => self::LIMIT_ALL
    ];
    $headers = [ 'Access-Control-Allow-Headers' => '*','Access-Control-Allow-Origin' => '*'];
    return new JsonResponse($data,200,$headers);
  }

  public function deleteData(){
    $this->student->deleteData();
    return new JsonResponse(['status'=>TRUE]);
  }


  ////
  ////
  //// FOR EXTERNAL SITE BELOW ALL
  ////
  ////
  public function checkLoginStatus(){
    $headers = [ 'Access-Control-Allow-Headers' => '*','Access-Control-Allow-Origin' => '*'];
    $roles = \Drupal::currentUser()->getRoles();
    if(in_array('teacher',$roles) || in_array('administrator',$roles)){
      return new JsonResponse(['status' => true],200);  
    }
    return new JsonResponse(['status' => false],200);
  }

  public function scanQrCode($qr){
    $headers = [ 'Access-Control-Allow-Headers' => '*','Access-Control-Allow-Origin' => '*'];

    if(empty($qr)){
      return new JsonResponse(['status' => false],200,$headers);
    }

    $data =  $this->student->sethash($qr)->getStudentDataByHash();
    if(empty($data)){
     return new JsonResponse(['status' => false],200,$headers); 
    }

    $this->storeRecentData($data);
    $this->storeDataFlag($data);
    
    return new JsonResponse(['status' => true,'data' => $data],200,$headers);
  }

  public function renderRecentStudent(){
    $headers = [ 'Access-Control-Allow-Headers' => '*','Access-Control-Allow-Origin' => '*'];
    $files = scandir(self::PATH);
    unset($files[0]);unset($files[1]);
    
    if(empty($files)){
       return new JsonResponse(['status' => false],200,$headers); 
    }

    $files = array_reverse($files);
    $data  = []; 
    foreach ($files as $key => $filename) {
      $json   = file_get_contents(self::PATH.'/'.$filename);
      $data[] = json_decode($json,TRUE);
      if($key == self::LIMIT - 1){
        break;
      }
    }

    return new JsonResponse(['status' => true,'data'=>$data],200,$headers); 
  }

  // DELETE CACHE / Folder
  public function deleteCache(){
    \Drupal::service('rest_api_access_token.cache')->deleteAll(); // delete cache rest api
    $files = scandir(self::PATH);
    unset($files[0]);unset($files[1]);
     
    foreach ($files as $key => $filename) {
       unlink(self::PATH.'/'.$filename); // delete all files inside STUDENTS Folder
    }
    return new JsonResponse(['status'=>True]);
  }

  // FOR DISPLAYING RECENT
  private function storeRecentData(&$data){
    $file = fopen(self::PATH.'/'.$data['ts'],'a');
    fwrite($file,json_encode($data));
    fclose($file);
  }

  // FOR DETECTING SCANNED MEMBERS
  private function storeDataFlag(&$data){
    $date = date("Y-m-d",time());
    $date = str_replace('-','_',$date);
    $filename = $date.'.json';
    if(file_exists(self::PATH_FLAG.'/'.$filename)){
      $file  = fopen(self::PATH_FLAG.'/'.$filename,'r');
      $dataf = fread($file,100000000);
      fclose($file);
      $dataf = json_decode($dataf,TRUE);
    }
    else{
      $dataf = [];
    }

    $file  = fopen(self::PATH_FLAG.'/'.$filename,'w');
    $dataf[] =  [
      'uid'  => $data['uid'],
      'hash' => $data['hash']
    ];
    fwrite($file,json_encode($dataf));
    fclose($file);
  }

}
