<?php

namespace Drupal\event\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\event\EventInterface;
use Symfony\Component\Routing\Route;

/**
 * Provides an access checker for event revisions.
 *
 * @ingroup event_access
 */
class EventRevisionAccessCheck implements AccessInterface {

  /**
   * The event storage.
   *
   * @var \Drupal\event\EventStorageInterface
   */
  protected $eventStorage;

  /**
   * The event access control handler.
   *
   * @var \Drupal\Core\Entity\EntityAccessControlHandlerInterface
   */
  protected $eventAccess;

  /**
   * A static cache of access checks.
   *
   * @var array
   */
  protected $access = [];

  /**
   * Constructs a new EventRevisionAccessCheck.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->eventStorage = $entity_manager->getStorage('event');
    $this->eventAccess = $entity_manager->getAccessControlHandler('event');
  }

  /**
   * Checks routing access for the event revision.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The route to check against.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   * @param int $event_revision
   *   (optional) The event revision ID. If not specified, but $event is, access
   *   is checked for that object's revision.
   * @param \Drupal\event\EventInterface $event
   *   (optional) A event object. Used for checking access to a event's default
   *   revision when $event_revision is unspecified. Ignored when $event_revision
   *   is specified. If neither $event_revision nor $event are specified, then
   *   access is denied.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(Route $route, AccountInterface $account, $event_revision = NULL, EventInterface $event = NULL) {
    if ($event_revision) {
      $event = $this->eventStorage->loadRevision($event_revision);
    }
    $operation = $route->getRequirement('_access_event_revision');
    return AccessResult::allowedIf($event && $this->checkAccess($event, $account, $operation))->cachePerPermissions()->addCacheableDependency($event);
  }

  /**
   * Checks event revision access.
   *
   * @param \Drupal\event\EventInterface $event
   *   The event to check.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   A user object representing the user for whom the operation is to be
   *   performed.
   * @param string $op
   *   (optional) The specific operation being checked. Defaults to 'view.'
   *
   * @return bool
   *   TRUE if the operation may be performed, FALSE otherwise.
   */
  public function checkAccess(EventInterface $event, AccountInterface $account, $op = 'view') {
    $map = [
      'view' => 'view all revisions',
      'update' => 'revert all revisions',
      'delete' => 'delete all revisions',
    ];
    $bundle = $event->bundle();
    $type_map = [
      'view' => "view $bundle revisions",
      'update' => "revert $bundle revisions",
      'delete' => "delete $bundle revisions",
    ];

    if (!$event || !isset($map[$op]) || !isset($type_map[$op])) {
      // If there was no event to check against, or the $op was not one of the
      // supported ones, we return access denied.
      return FALSE;
    }

    // Statically cache access by revision ID, language code, user account ID,
    // and operation.
    $langcode = $event->language()->getId();
    $cid = $event->getRevisionId() . ':' . $langcode . ':' . $account->id() . ':' . $op;

    if (!isset($this->access[$cid])) {
      // Perform basic permission checks first.
      if (!$account->hasPermission($map[$op]) && !$account->hasPermission($type_map[$op]) && !$account->hasPermission('administer events')) {
        $this->access[$cid] = FALSE;
        return FALSE;
      }

      // There should be at least two revisions. If the vid of the given event
      // and the vid of the default revision differ, then we already have two
      // different revisions so there is no need for a separate database check.
      // Also, if you try to revert to or delete the default revision, that's
      // not good.
      if ($event->isDefaultRevision() && ($this->eventStorage->countDefaultLanguageRevisions($event) == 1 || $op == 'update' || $op == 'delete')) {
        $this->access[$cid] = FALSE;
      }
      elseif ($account->hasPermission('administer events')) {
        $this->access[$cid] = TRUE;
      }
      else {
        // First check the access to the default revision and finally, if the
        // event passed in is not the default revision then access to that, too.
        $this->access[$cid] = $this->eventAccess->access($this->eventStorage->load($event->id()), $op, $account) && ($event->isDefaultRevision() || $this->eventAccess->access($event, $op, $account));
      }
    }

    return $this->access[$cid];
  }

}
