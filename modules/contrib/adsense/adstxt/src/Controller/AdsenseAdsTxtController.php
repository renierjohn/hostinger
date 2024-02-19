<?php

namespace Drupal\adsense_adstxt\Controller;

use Drupal\adsense\PublisherId;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller for the ads.txt file.
 */
class AdsenseAdsTxtController extends ControllerBase {

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a new CseV2ResultsController controller.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface|null $module_handler
   *   The module handler.
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler')
    );
  }

  /**
   * Display the ads.txt page.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Plain text response with the ads.txt content.
   */
  public function display() {
    $client = PublisherId::get();
    $this->moduleHandler->alter('adsense', $client);

    if (!empty($client)) {
      $content = "google.com, $client, DIRECT, f08c47fec0942fa0\n";
      return new Response($content, 200, ['Content-Type' => 'text/plain']);
    }

    throw new NotFoundHttpException();
  }

}
