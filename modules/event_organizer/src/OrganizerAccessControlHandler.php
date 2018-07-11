<?php

namespace Drupal\event_organizer;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Organizer entity.
 *
 * @see \Drupal\event_organizer\Entity\Organizer.
 */
class OrganizerAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\event_organizer\Entity\OrganizerInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished organizer entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published organizer entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit organizer entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete organizer entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add organizer entities');
  }

}
