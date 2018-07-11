<?php

namespace Drupal\event_venue;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Venue entity.
 *
 * @see \Drupal\event_venue\Entity\Venue.
 */
class VenueAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\event_venue\Entity\VenueInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished venue entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published venue entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit venue entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete venue entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add venue entities');
  }

}
