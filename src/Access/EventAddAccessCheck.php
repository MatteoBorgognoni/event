<?php

namespace Drupal\event\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\event\EventTypeInterface;

/**
 * Determines access to for event add pages.
 *
 * @ingroup event_access
 */
class EventAddAccessCheck implements AccessInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a EntityCreateAccessCheck object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * Checks access to the event add page for the event type.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   * @param \Drupal\event\EventTypeInterface $event_type
   *   (optional) The event type. If not specified, access is allowed if there
   *   exists at least one event type for which the user may create a event.
   *
   * @return string
   *   A \Drupal\Core\Access\AccessInterface constant value.
   */
  public function access(AccountInterface $account, EventTypeInterface $event_type = NULL) {
    $access_control_handler = $this->entityManager->getAccessControlHandler('event');
    // If checking whether a event of a particular type may be created.
    if ($account->hasPermission('administer event types')) {
      return AccessResult::allowed()->cachePerPermissions();
    }
    if ($event_type) {
      return $access_control_handler->createAccess($event_type->id(), $account, [], TRUE);
    }
    // If checking whether a event of any type may be created.
    foreach ($this->entityManager->getStorage('event_type')->loadMultiple() as $event_type) {
      if (($access = $access_control_handler->createAccess($event_type->id(), $account, [], TRUE)) && $access->isAllowed()) {
        return $access;
      }
    }

    // No opinion.
    return AccessResult::neutral();
  }

}
