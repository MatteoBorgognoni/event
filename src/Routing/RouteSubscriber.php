<?php

namespace Drupal\event\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // As events are the primary type of content, the event listing should be
    // easily available. In order to do that, override admin/content to show
    // a event listing instead of the path's child links.
//    $route = $collection->get('system.admin_content');
//    if ($route) {
//      $route->setDefaults([
//        '_title' => 'Events',
//        '_entity_list' => 'event',
//      ]);
//      $route->setRequirements([
//        '_permission' => 'access event overview',
//      ]);
//    }
  }

}
