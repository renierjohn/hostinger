<?php

namespace Drupal\data_router\Service;

use Drupal\Core\Entity\FloodService;
use PHPMailer\PHPMailer\PHPMailer;
use Drupal\Core\Entity\EntityTypeManager;

class AjaxRoute
{

  protected $entityTypeManager;

  protected $contents;

  const BUNDLE        = 'special_route';

  public function __construct(EntityTypeManager $entityTypeManager){
    $this->entityTypeManager = $entityTypeManager;
  	$this->contents 		 = $entityTypeManager->getStorage('node')->loadByProperties(['type' => self::BUNDLE,'status'=>1]);
  }

  private function getOrigin(){
    $routes  =  $this->contents;
  	$contents = [];
  	foreach ($routes as $nid => $route) {
  		$contents[$route->field_special_route_origin->value] = $route->field_special_route_origin->value;		
  	}
  	return $contents;
  }

  private function getDestination(){
  	$routes  =  $this->contents;
  	$contents = [];
  	foreach ($routes as $nid => $route) {
  		$contents[$route->field_special_route_destination->value] = $route->field_special_route_destination->value;		
  	}
  	return $contents;
  }

  public function getRouteLists(){
  	$routes   =  $this->contents;
  	$contents = [];
  	foreach ($routes as $nid => $route) {
  			$contents[] = [
  				'origin' => $route->field_special_route_origin->value,
  				'dest'   => $route->field_special_route_destination->value,
  				'id'     => $route->field_special_route_id->value,
  			];
  	}
    return [
      'origin' => $this->getOrigin(),
      'dest'   => $this->getDestination(),
      'content' => $contents
    ];
  }

}
