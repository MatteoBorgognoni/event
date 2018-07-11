<?php

namespace Drupal\event_venue;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class VenueStorage extends SqlContentEntityStorage implements VenueStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(VenueInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {venue_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {venue_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(VenueInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {venue_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('venue_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
