<?php

namespace Drupal\event_content;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Event content entity.
 *
 * @see \Drupal\event_content\Entity\EventContent.
 */
class EventContentAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\event_content\Entity\EventContentInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished event content entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published event content entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit event content entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete event content entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add event content entities');
  }

}
