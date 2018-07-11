<?php

namespace Drupal\event\Plugin\EntityReferenceSelection;

use Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection;
use Drupal\event\EventInterface;

/**
 * Provides specific access control for the event entity type.
 *
 * @EntityReferenceSelection(
 *   id = "default:event",
 *   label = @Translation("Event selection"),
 *   entity_types = {"event"},
 *   group = "default",
 *   weight = 1
 * )
 */
class EventSelection extends DefaultSelection {

  /**
   * {@inheritdoc}
   */
  protected function buildEntityQuery($match = NULL, $match_operator = 'CONTAINS') {
    $query = parent::buildEntityQuery($match, $match_operator);
    // Adding the 'event_access' tag is sadly insufficient for events: core
    // requires us to also know about the concept of 'published' and
    // 'unpublished'. We need to do that as long as there are no access control
    // modules in use on the site. As long as one access control module is there,
    // it is supposed to handle this check.
    if (!$this->currentUser->hasPermission('bypass event access') && !count($this->moduleHandler->getImplementations('event_grants'))) {
      $query->condition('status', EventInterface::PUBLISHED);
    }
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function createNewEntity($entity_type_id, $bundle, $label, $uid) {
    $event = parent::createNewEntity($entity_type_id, $bundle, $label, $uid);

    // In order to create a referenceable event, it needs to published.
    /** @var \Drupal\event\EventInterface $event */
    $event->setPublished(TRUE);

    return $event;
  }

  /**
   * {@inheritdoc}
   */
  public function validateReferenceableNewEntities(array $entities) {
    $entities = parent::validateReferenceableNewEntities($entities);
    // Mirror the conditions checked in buildEntityQuery().
    if (!$this->currentUser->hasPermission('bypass event access') && !count($this->moduleHandler->getImplementations('event_grants'))) {
      $entities = array_filter($entities, function ($event) {
        /** @var \Drupal\event\EventInterface $event */
        return $event->isPublished();
      });
    }
    return $entities;
  }

}
