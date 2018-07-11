<?php

namespace Drupal\event;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;

/**
 * Defines the storage handler class for events.
 *
 * This extends the base storage class, adding required special handling for
 * event entities.
 */
class EventStorage extends SqlContentEntityStorage implements EventStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisioeids(EventInterface $event) {
    return $this->database->query(
      'SELECT vid FROM {event_revision} WHERE eid=:eid ORDER BY vid',
      [':eid' => $event->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisioeids(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {event_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(EventInterface $event) {
    return $this->database->query('SELECT COUNT(*) FROM {event_field_revision} WHERE eid = :eid AND default_langcode = 1', [':eid' => $event->id()])->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function updateType($old_type, $new_type) {
    return $this->database->update('event')
      ->fields(['type' => $new_type])
      ->condition('type', $old_type)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('event_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
