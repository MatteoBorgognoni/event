<?php

namespace Drupal\event_organizer;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class OrganizerStorage extends SqlContentEntityStorage implements OrganizerStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(OrganizerInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {organizer_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {organizer_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(OrganizerInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {organizer_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('organizer_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
