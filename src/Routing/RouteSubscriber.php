<?php

namespace Drupal\legacy_redirect\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    // Override "system.404".
    if ($route = $collection->get('system.404')) {
      $route->setDefaults([
        '_controller' => 'Drupal\legacy_redirect\Controller\RedirectController::legacyRedirect',
      ]);
    }
  }

}
