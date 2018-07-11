<?php

namespace Drupal\event;

use Drupal\Core\Session\AccountInterface;

/**
 * Event specific entity access control methods.
 *
 * @ingroup event_access
 */
interface EventAccessControlHandlerInterface {

  /**
   * Gets the list of event access grants.
   *
   * This function is called to check the access grants for a event. It collects
   * all event access grants for the event from hook_event_access_records()
   * implementations, allows these grants to be altered via
   * hook_event_access_records_alter() implementations, and returns the grants to
   * the caller.
   *
   * @param \Drupal\event\EventInterface $event
   *   The $event to acquire grants for.
   *
   * @return array
   *   The access rules for the event.
   */
  public function acquireGrants(EventInterface $event);

  /**
   * Writes a list of grants to the database, deleting any previously saved ones.
   *
   * Modules that use event access can use this function when doing mass updates
   * due to widespread permission changes.
   *
   * Note: Don't call this function directly from a contributed module. Call
   * \Drupal\event\EventAccessControlHandlerInterface::acquireGrants() instead.
   *
   * @param \Drupal\event\EventInterface $event
   *   The event whose grants are being written.
   * @param $delete
   *   (optional) If false, does not delete records. This is only for optimization
   *   purposes, and assumes the caller has already performed a mass delete of
   *   some form. Defaults to TRUE.
   *
   * @deprecated in Drupal 8.x, will be removed before Drupal 9.0.
   *   Use \Drupal\event\EventAccessControlHandlerInterface::acquireGrants().
   */
  public function writeGrants(EventInterface $event, $delete = TRUE);

  /**
   * Creates the default event access grant entry on the grant storage.
   */
  public function writeDefaultGrant();

  /**
   * Deletes all event access entries.
   */
  public function deleteGrants();

  /**
   * Counts available event grants.
   *
   * @return int
   *   Returns the amount of event grants.
   */
  public function countGrants();

  /**
   * Checks all grants for a given account.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   A user object representing the user for whom the operation is to be
   *   performed.
   *
   * @return int
   *   Status of the access check.
   */
  public function checkAllGrants(AccountInterface $account);

}
