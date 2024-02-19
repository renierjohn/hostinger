<?php

namespace Drupal\adsense\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ModuleExtensionList;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Controller for the Custom Search Engine results page.
 */
class CseResultsController extends ControllerBase {

  /**
   * The request stack used to access request globals.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The module extension list.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected $moduleExtensionList;

  /**
   * Constructs a new CseV2ResultsController controller.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Extension\ModuleExtensionList $extension_list_module
   *   The module extension list.
   */
  public function __construct(RequestStack $request_stack, ModuleExtensionList $extension_list_module) {
    $this->requestStack = $request_stack;
    $this->moduleExtensionList = $extension_list_module;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('extension.list.module')
    );
  }

  /**
   * Display the search results page.
   *
   * @return array
   *   Markup for the page with the search results.
   */
  public function display() {
    $config = $this->config('adsense.settings');
    $width = $config->get('adsense_cse_frame_width');
    $country = $config->get('adsense_cse_country');

    if ($config->get('adsense_test_mode')) {
      $content = [
        '#theme' => 'adsense_ad',
        '#content' => ['#markup' => nl2br("Results\nwidth = $width\ncountry = $country")],
        '#classes' => ['adsense-placeholder'],
        '#width' => $width,
        '#height' => 100,
      ];
    }
    else {
      global $base_url;

      // Log the search keys.
      $this->getLogger('AdSense CSE v1')->notice('Search keywords: %keyword', [
        '%keyword' => urldecode($this->requestStack->getCurrentRequest()->query->get('q')),
      ]);

      $content = [
        '#theme' => 'adsense_cse_results',
        '#width' => $width,
        '#country' => $country,
        // http://www.google.com/afsonline/show_afs_search.js
        '#script' => $base_url . '/' . $this->moduleExtensionList->getPath('adsense') . '/js/adsense_cse-v1.results.js',
      ];
    }
    return $content;
  }

}
