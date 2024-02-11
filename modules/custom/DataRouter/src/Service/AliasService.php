<?php

namespace Drupal\data_router\Service;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Database\Connection;

class AliasService
{
  protected $entityTypeManager;

  protected $database;

  protected $id;

  protected $bundle;

  protected $node;

  public function __construct(EntityTypeManager $entityTypeManager,Connection $database){
    $this->entityTypeManager = $entityTypeManager;
    $this->database          = $database;
  }

  public function setID($id){
    $this->id = $id;
    return $this;
  }

  public function setBundle($bundle){
    $this->bundle = $bundle;
    return $this;
  }

  public function setNode($node){
    $this->node = $node;
    return $this;
  }

  private function getNids(){
    $entity = $this->entityTypeManager;
    $node   = $entity->getStorage('node');
    $nid    = $node->getQuery();
    $nids   = $nid->condition('type',$this->bundle)
                  // ->condition('field_category',[1,4,3],'IN')
                  ->condition('status',1)
                  ->accessCheck(FALSE)
                  ->execute();
    return array_values($nids);
  }

  public function getContentPagination(){
    $entity = $this->entityTypeManager;
    $id     = $this->id;
    
    if(empty($id)){
      return FALSE;
    }

    $alias  = $entity->getStorage('path_alias');
    $nids   = $this->getNids();
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

  public function getRoutesRelation(){
    $entity = $this->entityTypeManager;
    $node   = $this->node;
    $e      = $entity->getStorage('node');
    $query  = $e->getQuery();
    $nid    = $node->id();
    $bundle = $node->bundle();
    
    $query->accessCheck(FALSE);
    $hasCocaliong  = $node->field_has_cocaliong_route->value;
    $bus_routes    = $node->field_tx_table_route;
    $vessel_routes = $node->field_vessel_destination;

    $list = [];
    if($hasCocaliong == '1'){
     $list[] = [
        'title' => 'Cocaliong',
        'image' => '/sites/default/files/public/barkot/ship.jpg',
        'link'  => '/vessel/cocaliong/',
     ]; 
    }
    
    foreach ($bus_routes as $bus_route) {
      $list[] = [
        'title' => $bus_route->entity->getTitle(),
        'image' => $bus_route->entity->field_banner_image->entity->uri->value ? str_replace('public://','/sites/default/files/public/',$bus_route->entity->field_banner_image->entity->uri->value) : '/sites/default/files/favicon.ico' ,
        'link'  => $bus_route->entity->path->alias.'/'
      ];
    }


    if(empty($vessel_routes->entity)){
      return $list;
    }  
    $query->condition('type','barkota_shipping_vessel');
    $or = $query->orConditionGroup();
    foreach ($vessel_routes as $vessel_route) {
      $id = $vessel_route->entity->id();
      $or->condition('field_vessel_destination',$id);
      
    }
    $query->condition($or);
    $nids    = $query->execute();
    if(empty($nids)){
      return $list;
    }
    $vessels = $e->loadMultiple($nids);
    foreach ($vessels as $vessel) {
      $list[] = [
        'title' => $vessel->getTitle(),
        'image' => $vessel->field_banner_image->entity->uri->value ? str_replace('public://','/sites/default/files/public/',$vessel->field_banner_image->entity->uri->value) : '/sites/default/files/favicon.ico' ,
        'link'  => $vessel->path->alias.'/'
      ];
    }
    return $list;
  }

}
