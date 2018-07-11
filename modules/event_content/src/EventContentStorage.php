<?php

namespace Drupal\event_content;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class EventContentStorage extends SqlContentEntityStorage implements EventContentStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(EventContentInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {event_content_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {event_content_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(EventContentInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {event_content_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('event_content_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
