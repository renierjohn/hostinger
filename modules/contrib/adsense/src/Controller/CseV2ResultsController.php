<?php

namespace Drupal\adsense\Controller;

use Drupal\adsense\PublisherId;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Controller for the Custom Search Engine v2 results page.
 */
class CseV2ResultsController extends ControllerBase {

  /**
   * The request stack used to access request globals.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a new CseV2ResultsController controller.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(RequestStack $request_stack) {
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')
    );
  }

  /**
   * Display the search results page.
   *
   * @param string $slot
   *   CSE slot ID.
   *
   * @return array
   *   Markup for the page with the search results.
   */
  public function display($slot) {
    $config = $this->config('adsense.settings');
    $client = PublisherId::get();
    $this->moduleHandler()->alter('adsense', $client);

    if ($config->get('adsense_test_mode')) {
      $content = [
        '#theme' => 'adsense_ad',
        '#content' => ['#markup' => nl2br("Results\ncx = partner-$client:{$slot}")],
        '#classes' => ['adsense-placeholder'],
        '#height' => 100,
      ];
    }
    else {
      // Log the search keys.
      $this->getLogger('AdSense CSE v2')->notice('Search keywords: %keyword', [
        '%keyword' => urldecode($this->requestStack->getCurrentRequest()->query->get('q')),
      ]);

      $content = [
        '#theme' => 'adsense_cse_v2_results',
        '#client' => $client,
        '#slot' => $slot,
      ];
    }
    return $content;
  }

}
