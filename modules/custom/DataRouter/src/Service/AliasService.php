<?php

namespace Drupal\data_router\Service;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Database\Connection;

class AliasService
{
  protected $entityTypeManager;

  protected $database;

  protected $id;

  public function __construct(EntityTypeManager $entityTypeManager,Connection $database){
    $this->entityTypeManager = $entityTypeManager;
    $this->database          = $database;
  }

  public function setID($id){
    $this->id = $id;
    return $this;
  }

  public function getContentPagination(){
    $entity = $this->entityTypeManager;
    $id     = $this->id;
    if(empty($id)){
      return FALSE;
    }
    $node   = $entity->getStorage('node');
    $alias  = $entity->getStorage('path_alias');
    $nid    = $node->getQuery();
    $nids   = $nid->condition('type','places')
                  ->condition('field_category',[1,4,3],'IN')
                  ->condition('status',1)
                  ->execute();
    $nids   = array_values($nids);
    $temp   = [];

    foreach ($nids as $key => $nid) {
      if($nid == $id){
        if($key == 0){
          $nid    = $nids[count($nids) - 1];
          $path   = '/node/'.$nid;
          $pathx  = $alias->loadByProperties(['path' => $path]);

          if(!empty($pathx)){
            $path = reset($pathx)->toArray()['alias'][0]['value'];
          }
          $temp['prev'] = $path.'/';
        }
        if($key > 0){
          $nid    = $nids[$key - 1];
          $path   = '/node/'.$nid;
          $pathx  = $alias->loadByProperties(['path' => $path]);

          if(!empty($pathx)){
            $path = reset($pathx)->toArray()['alias'][0]['value'];
          }
          $temp['prev'] = $path.'/';
        }

        if($key < count($nids) - 1){
          $nid    = $nids[$key + 1];
          $path   = '/node/'.$nid;
          $pathx  = $alias->loadByProperties(['path' => $path]);

          if(!empty($pathx)){
            $path = reset($pathx)->toArray()['alias'][0]['value'];
          }
          $temp['next'] = $path.'/';
        }
        if($key >= count($nids) - 1){
          $nid    = $nids[0];
          $path   = '/node/'.$nid;
          $pathx  = $alias->loadByProperties(['path' => $path]);

          if(!empty($pathx)){
            $path = reset($pathx)->toArray()['alias'][0]['value'];
          }
          $temp['next'] = $path.'/';
        }
      }
    }
    return $temp;
  }

}
