<?php

namespace Drupal\event_organizer;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\event_organizer\Entity\OrganizerInterface;

/**
 * Defines the storage handler class for Organizer entities.
 *
 * This extends the base storage class, adding required special handling for
 * Organizer entities.
 *
 * @ingroup event_organizer
 */
interface OrganizerStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Organizer revision IDs for a specific Organizer.
   *
   * @param \Drupal\event_organizer\Entity\OrganizerInterface $entity
   *   The Organizer entity.
   *
   * @return int[]
   *   Organizer revision IDs (in ascending order).
   */
  public function revisionIds(OrganizerInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Organizer author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Organizer revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\event_organizer\Entity\OrganizerInterface $entity
   *   The Organizer entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(OrganizerInterface $entity);

  /**
   * Unsets the language for all Organizer with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
