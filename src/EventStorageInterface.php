<?php

namespace Drupal\event;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines an interface for event entity storage classes.
 */
interface EventStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of event revision IDs for a specific event.
   *
   * @param \Drupal\event\EventInterface $event
   *   The event entity.
   *
   * @return int[]
   *   Event revision IDs (in ascending order).
   */
  public function revisioeids(EventInterface $event);

  /**
   * Gets a list of revision IDs having a given user as event author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Event revision IDs (in ascending order).
   */
  public function userRevisioeids(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\event\EventInterface $event
   *   The event entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(EventInterface $event);

  /**
   * Updates all events of one type to be of another type.
   *
   * @param string $old_type
   *   The current event type of the events.
   * @param string $new_type
   *   The new event type of the events.
   *
   * @return int
   *   The number of events whose event type field was modified.
   */
  public function updateType($old_type, $new_type);

  /**
   * Unsets the language for all events with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
