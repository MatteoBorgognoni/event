<?php

namespace Drupal\event_content\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Event content entities.
 *
 * @ingroup event_content
 */
interface EventContentInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Event content name.
   *
   * @return string
   *   Name of the Event content.
   */
  public function getName();

  /**
   * Sets the Event content name.
   *
   * @param string $name
   *   The Event content name.
   *
   * @return \Drupal\event_content\Entity\EventContentInterface
   *   The called Event content entity.
   */
  public function setName($name);

  /**
   * Gets the Event content creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Event content.
   */
  public function getCreatedTime();

  /**
   * Sets the Event content creation timestamp.
   *
   * @param int $timestamp
   *   The Event content creation timestamp.
   *
   * @return \Drupal\event_content\Entity\EventContentInterface
   *   The called Event content entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Event content published status indicator.
   *
   * Unpublished Event content are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Event content is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Event content.
   *
   * @param bool $published
   *   TRUE to set this Event content to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\event_content\Entity\EventContentInterface
   *   The called Event content entity.
   */
  public function setPublished($published);

  /**
   * Gets the Event content revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Event content revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\event_content\Entity\EventContentInterface
   *   The called Event content entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Event content revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Event content revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\event_content\Entity\EventContentInterface
   *   The called Event content entity.
   */
  public function setRevisionUserId($uid);

}
