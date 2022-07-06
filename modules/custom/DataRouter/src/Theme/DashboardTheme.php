<?php
namespace Drupal\data_router\Theme;

use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\Routing\Route;
use Drupal\Core\Theme\ThemeNegotiatorInterface;

/**
 * Our Gianduja Theme Negotiator
 */
class DashboardTheme implements ThemeNegotiatorInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $route = $route_match->getRouteObject();

    if(!empty($route) && $route->getPath() == '/dashboard' && \Drupal::currentUser()->id() != 0){
        return TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    return 'renify_dashboard';
  }
}
