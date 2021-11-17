<?php

namespace Drupal\data_router\Service;

use Drupal\Core\Entity\FloodService;
use Drupal\Core\Entity\EntityTypeManager;

class FloodService
{
  protected $entityTypeManager;

  public function __construct(EntityTypeManager $entityTypeManager){
    $this->entityTypeManager = $entityTypeManager;
  }

}
