<?php

namespace Drupal\event_content;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\event_content\Entity\EventContentInterface;

/**
 * Defines the storage handler class for Event content entities.
 *
 * This extends the base storage class, adding required special handling for
 * Event content entities.
 *
 * @ingroup event_content
 */
interface EventContentStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Event content revision IDs for a specific Event content.
   *
   * @param \Drupal\event_content\Entity\EventContentInterface $entity
   *   The Event content entity.
   *
   * @return int[]
   *   Event content revision IDs (in ascending order).
   */
  public function revisionIds(EventContentInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Event content author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Event content revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\event_content\Entity\EventContentInterface $entity
   *   The Event content entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(EventContentInterface $entity);

  /**
   * Unsets the language for all Event content with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
