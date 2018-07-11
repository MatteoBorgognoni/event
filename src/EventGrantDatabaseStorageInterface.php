<?php

namespace Drupal\event;

use Drupal\Core\Session\AccountInterface;

/**
 * Provides an interface for event access grant storage.
 *
 * @ingroup event_access
 */
interface EventGrantDatabaseStorageInterface {

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
  public function checkAll(AccountInterface $account);

  /**
   * Alters a query when event access is required.
   *
   * @param mixed $query
   *   Query that is being altered.
   * @param array $tables
   *   A list of tables that need to be part of the alter.
   * @param string $op
   *   The operation to be performed on the event. Possible values are:
   *   - "view"
   *   - "update"
   *   - "delete"
   *   - "create"
   * @param \Drupal\Core\Session\AccountInterface $account
   *   A user object representing the user for whom the operation is to be
   *   performed.
   * @param string $base_table
   *   The base table of the query.
   *
   * @return int
   *   Status of the access check.
   */
  public function alterQuery($query, array $tables, $op, AccountInterface $account, $base_table);

  /**
   * Writes a list of grants to the database, deleting previously saved ones.
   *
   * If a realm is provided, it will only delete grants from that realm, but
   * it will always delete a grant from the 'all' realm. Modules that use
   * event access can use this method when doing mass updates due to widespread
   * permission changes.
   *
   * Note: Don't call this method directly from a contributed module. Call
   * \Drupal\event\EventAccessControlHandlerInterface::acquireGrants() instead.
   *
   * @param \Drupal\event\EventInterface $event
   *   The event whose grants are being written.
   * @param array $grants
   *   A list of grants to write. Each grant is an array that must contain the
   *   following keys: realm, gid, grant_view, grant_update, grant_delete.
   *   The realm is specified by a particular module; the gid is as well, and
   *   is a module-defined id to define grant privileges. each grant_* field
   *   is a boolean value.
   * @param string $realm
   *   (optional) If provided, read/write grants for that realm only. Defaults to
   *   NULL.
   * @param bool $delete
   *   (optional) If false, does not delete records. This is only for optimization
   *   purposes, and assumes the caller has already performed a mass delete of
   *   some form. Defaults to TRUE.
   */
  public function write(EventInterface $event, array $grants, $realm = NULL, $delete = TRUE);

  /**
   * Deletes all event access entries.
   */
  public function delete();

  /**
   * Creates the default event access grant entry.
   */
  public function writeDefault();

  /**
   * Determines access to events based on event grants.
   *
   * @param \Drupal\event\EventInterface $event
   *   The entity for which to check 'create' access.
   * @param string $operation
   *   The entity operation. Usually one of 'view', 'edit', 'create' or
   *   'delete'.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user for which to check access.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result, either allowed or neutral. If there are no event
   *   grants, the default grant defined by writeDefault() is applied.
   *
   * @see hook_event_grants()
   * @see hook_event_access_records()
   * @see \Drupal\event\EventGrantDatabaseStorageInterface::writeDefault()
   */
  public function access(EventInterface $event, $operation, AccountInterface $account);

  /**
   * Counts available event grants.
   *
   * @return int
   *   Returns the amount of event grants.
   */
  public function count();

  /**
   * Remove the access records belonging to certain events.
   *
   * @param array $eids
   *   A list of event IDs. The grant records belonging to these events will be
   *   deleted.
   */
  public function deleteEventRecords(array $eids);

}
