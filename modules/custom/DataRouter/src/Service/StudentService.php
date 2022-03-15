<?php

namespace Drupal\data_router\Service;

use Drupal\Core\Entity\EntityTypeManager;

class StudentService
{

  protected $entityTypeManager;

  protected $hash;

  const ROLE = 'student';

  public function __construct(EntityTypeManager $entityTypeManager){
    $this->entityTypeManager = $entityTypeManager;
  }

  public function setHash($hash){
    $this->hash = $hash;
    return $this;
  }

  public function getData(){
    $hash = $this->hash;
    $user = $this->entityTypeManager->getStorage('user')->loadByProperties(['roles'=>'student','field_qr'=>$hash]);
    if(empty($user)){
      return FALSE;
    }

    $user = reset($user);

    $data = [
      'hash'  => $user->field_qr->value,
      'name'  => $user->name->value, 
      'image' => $this->getUrl($user->user_picture->target_id),
      'ts'    => date("Y-m-d h:i A",time()),
      'hour'  => date("h",time()),
      'minute'=> date("i",time()),
      'ampm'  => date("A",time()),
    ];
    return $data;
  }

  public function generateHash(){

  }

  private function getUrl($fid){
    $file = $this->entityTypeManager->getStorage('file')->load($fid);
    if(empty($file)){
      return FALSE;
    }
    $uri =  $file->getFileUri();
    $uri = str_replace('public://', '/sites/default/files/public/',$uri);
    return $uri;
  }

}
