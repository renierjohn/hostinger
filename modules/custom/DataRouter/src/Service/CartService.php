<?php

namespace Drupal\data_router\Service;

use Drupal\Core\Entity\FloodService;
use PHPMailer\PHPMailer\PHPMailer;
use Drupal\Core\Entity\EntityTypeManager;

class CartService
{

  protected $entityTypeManager;

  protected $contents;

  const BUNDLE        = 'products';

  public function __construct(EntityTypeManager $entityTypeManager){
    $this->entityTypeManager = $entityTypeManager;
  	$this->contents 		 = $entityTypeManager->getStorage('node')->loadByProperties(['type' => self::BUNDLE,'status'=>1]);
  }

  public function time(){
  	return time();
  }
  public function gets(){
    \Drupal::service('');

  }

}
