<?php
namespace Drupal\data_router\Theme;

use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\Routing\Route;
use Drupal\Core\Theme\ThemeNegotiatorInterface;

/**
 * Our Gianduja Theme Negotiator
 */
class ImceOverrideTheme implements ThemeNegotiatorInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $route = $route_match->getRouteObject();

    if(!empty($route) && $route->getPath() == '/imce/{scheme}'){
        return TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    return 'adminimal_theme';
  }
}
