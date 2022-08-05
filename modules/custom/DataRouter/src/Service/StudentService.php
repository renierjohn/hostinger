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
    $this->storeHash($user->field_qr->value,$user->id());
    $data = [
      'hash'  => $user->field_qr->value,
      'level' => $user->field_level->value,
      'gender'=> $user->field_gender->value,
      'name'  => $user->name->value,
      'image' => $this->getUrl($user->user_picture->target_id),
      'ts'    => date("Y-m-d h:i A",time()),
      'hour'  => date("h",time()),
      'minute'=> date("i",time()),
      'ampm'  => date("A",time()),
    ];
    return $data;
  }

  private function getUrl($fid){
    if(empty($fid)){
      return False;
    }
    $file = $this->entityTypeManager->getStorage('file')->load($fid);
    if(empty($file)){
      return FALSE;
    }
    $uri =  $file->getFileUri();
    $uri = str_replace('public://', '/sites/default/files/public/',$uri);
    return $uri;
  }

  public function generateHash($name){
    return 'renify-'.md5($name.time());
  }

  public function deleteData(){
      $filename = 'private://'.self::ROLE.'.json';
      file_put_contents($filename,json_encode([]));
  }

  private function storeHash($hash,$id){
    $filename = 'private://'.self::ROLE.'.json';
    $json_hash = file_get_contents($filename);
    
    if(empty($json_hash)){
      $json_hash[] = [
        'hash' => $hash,
        'id'   => $id,
      ];
      file_put_contents($filename,json_encode($json_hash));
      return;
    }

    $json_hash = json_decode($json_hash,True);
    $hash_arr  = array_column($json_hash,'hash');
    if(!in_array($hash,$hash_arr)){
      $json_hash[] = [
        'hash' => $hash,
        'id'   => $id,
      ];
      file_put_contents($filename,json_encode($json_hash));
    }
  }

  public function getLatestHash(){
    $filename  = 'private://'.self::ROLE.'.json';
    $json_hash = file_get_contents($filename);
    $hash      = json_decode($json_hash,True);
    if(!empty($hash)){
      if(count($hash) > 3){
        $hash = array_reverse($hash);
        return array_splice($hash,0,3);
      }
    }
    return $hash;
  }

  public function isHashExists($hash){
    $filename  = 'private://'.self::ROLE.'.json';
    $json_hash = file_get_contents($filename);
    $json_hash = json_decode($json_hash,True);
    $json_hash = array_column($json_hash,'hash');
    if(in_array($hash,$json_hash)){
      return True;
    }
    return False;
  }

  public function query($limit,$start,$gender = False,$level = False,$present = False){
    $query = $this->entityTypeManager->getStorage('user')->getQuery();
    $query->condition('roles','student');
    if(empty($present)){
      $query->range($start,$limit + 1);
    }

    if(!empty($gender)){
      $query->condition('field_gender',$gender);
    }

    if(!empty($level)){
      $query->condition('field_level',$level);
    }

    $ids = $query->execute();

    if($present == 1){
      $ids = $this->filterIds(array_values($ids),1);
    }

    if($present == 2 && !empty($present)){
      $ids = $this->filterIds(array_values($ids),2);
    }

    if(!empty($present) && count($ids) > $limit){
      $ids = array_splice($ids,0,$limit + 1);
    }

    $users = $this->entityTypeManager->getStorage('user')->loadMultiple($ids);
    $users_arr = [];
    foreach($users as $user){
      $users_arr[] = [
        'id'    => $user->id(),
        'hash'  => $user->field_qr->value,
        'level' => $user->field_level->value,
        'gender'=> $user->field_gender->value,
        'name'  => $user->name->value,
        'image' => $this->getUrl($user->user_picture->target_id),
        'flag'  => $this->getFlag($user->id()),
      ];
    }
    return $users_arr;
  }

  public function getLevels(){
    $query = $this->entityTypeManager->getStorage('user')->getQuery();
    $query->condition('roles','student');
    $ids = $query->execute();
    $users = $this->entityTypeManager->getStorage('user')->loadMultiple($ids);
    $users_arr;
    foreach($users as $user){
      if($user->field_level->value == 0){
        continue;
      }
      $users_arr[$user->field_level->value][$user->field_gender->value][] = True;
      $users_arr[$user->field_level->value]['level'] = $user->field_level->value;
    }
    ksort($users_arr);
    return $users_arr;
  }

  private function filterIds($ids,$type){
    $filename  = 'private://'.self::ROLE.'.json';
    $json_hash = file_get_contents($filename);
    $json_hash = json_decode($json_hash,True);
    $json_id   = array_column($json_hash,'id');
    if($type == 1){
      return array_intersect($ids,$json_id);
    }
    if($type == 2){
      return array_diff($ids,$json_id);
    }
  }

  private function getFlag($id){ 
    $filename  = 'private://'.self::ROLE.'.json';
    $json_hash = file_get_contents($filename);
    if(empty($json_hash)){
      return False;
    }
    $json_hash = json_decode($json_hash,True);
    $json_id   = array_column($json_hash,'id');
    if(in_array($id,$json_id)){
      return True;
    }
    return False;
  }

  ////
  ////
  //// FOR EXTERNAL SITE BELOW ALL
  ////
  ////
  public function getStudentDataByHash(){
    $hash = $this->hash;
    $user = $this->entityTypeManager->getStorage('user')->loadByProperties(['roles'=>'student','field_qr'=>$hash]);
    if(empty($user)){
      return FALSE;
    }

    $user = reset($user);
    $timeStamp = time();
    $data = [
      'uid'   => $user->id(),
      'hash'  => $user->field_qr->value,
      'level' => $user->field_level->value,
      'gender'=> $user->field_gender->value,
      'name'  => $user->name->value,
      'image' => $this->getUrl($user->user_picture->target_id),
      'ts'    => $timeStamp,
      'dt'    => date("Y-m-d h:i A",$timeStamp),
      'hour'  => date("h",time()),
      'minute'=> date("i",time()),
      'ampm'  => date("A",time()),
    ];
    return $data;
  }

}
