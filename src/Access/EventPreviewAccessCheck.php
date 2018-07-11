<?php

namespace Drupal\event\Access;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\event\EventInterface;

/**
 * Determines access to event previews.
 *
 * @ingroup event_access
 */
class EventPreviewAccessCheck implements AccessInterface {

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
   * Checks access to the event preview page.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   * @param \Drupal\event\EventInterface $event_preview
   *   The event that is being previewed.
   *
   * @return string
   *   A \Drupal\Core\Access\AccessInterface constant value.
   */
  public function access(AccountInterface $account, EventInterface $event_preview) {
    if ($event_preview->isNew()) {
      $access_controller = $this->entityManager->getAccessControlHandler('event');
      return $access_controller->createAccess($event_preview->bundle(), $account, [], TRUE);
    }
    else {
      return $event_preview->access('update', $account, TRUE);
    }
  }

}
