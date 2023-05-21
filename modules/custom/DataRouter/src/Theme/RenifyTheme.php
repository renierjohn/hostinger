<?php
namespace Drupal\data_router\Theme;

use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\Routing\Route;
use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Drupal\node\NodeInterface;
/**
 * Renify Theme Negotiator
 */
class RenifyTheme implements ThemeNegotiatorInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $route = $route_match->getRouteObject();

    if(!empty($route) && $route->getPath() == '/user/login'){
        return TRUE;
    }
    $type = '';

    if (($node = \Drupal::routeMatch()->getParameter('node'))) {
      if ($node instanceof NodeInterface) {
        $type = $node->getType();
      }
    }

    if($type === 'places' || $type === 'barkota_shipping_vessel' || $type == 'routes'){
        return TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    return 'renify';
  }
}
