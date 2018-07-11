<?php

namespace Drupal\event_venue;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\event_venue\Entity\VenueInterface;

/**
 * Defines the storage handler class for Venue entities.
 *
 * This extends the base storage class, adding required special handling for
 * Venue entities.
 *
 * @ingroup event_venue
 */
interface VenueStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Venue revision IDs for a specific Venue.
   *
   * @param \Drupal\event_venue\Entity\VenueInterface $entity
   *   The Venue entity.
   *
   * @return int[]
   *   Venue revision IDs (in ascending order).
   */
  public function revisionIds(VenueInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Venue author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Venue revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\event_venue\Entity\VenueInterface $entity
   *   The Venue entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(VenueInterface $entity);

  /**
   * Unsets the language for all Venue with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
