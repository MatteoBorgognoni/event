<?php

namespace Drupal\event\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\EntityRouteProviderInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Provides routes for events.
 */
class EventRouteProvider implements EntityRouteProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    $route_collection = new RouteCollection();
    $route = (new Route('/event/{event}'))
      ->addDefaults([
        '_controller' => '\Drupal\event\Controller\EventViewController::view',
        '_title_callback' => '\Drupal\event\Controller\EventViewController::title',
      ])
      ->setRequirement('event', '\d+')
      ->setRequirement('_entity_access', 'event.view');
    $route_collection->add('entity.event.canonical', $route);

    $route = (new Route('/event/{event}/delete'))
      ->addDefaults([
        '_entity_form' => 'event.delete',
        '_title' => 'Delete',
      ])
      ->setRequirement('event', '\d+')
      ->setRequirement('_entity_access', 'event.delete')
      ->setOption('_event_operation_route', TRUE);
    $route_collection->add('entity.event.delete_form', $route);

    $route = (new Route('/event/{event}/edit'))
      ->setDefault('_entity_form', 'event.edit')
      ->setRequirement('_entity_access', 'event.update')
      ->setRequirement('event', '\d+')
      ->setOption('_event_operation_route', TRUE);
    $route_collection->add('entity.event.edit_form', $route);

    return $route_collection;
  }

}
