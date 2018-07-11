<?php

namespace Drupal\event_organizer\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Organizer entities.
 *
 * @ingroup event_organizer
 */
interface OrganizerInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Organizer name.
   *
   * @return string
   *   Name of the Organizer.
   */
  public function getName();

  /**
   * Sets the Organizer name.
   *
   * @param string $name
   *   The Organizer name.
   *
   * @return \Drupal\event_organizer\Entity\OrganizerInterface
   *   The called Organizer entity.
   */
  public function setName($name);

  /**
   * Gets the Organizer creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Organizer.
   */
  public function getCreatedTime();

  /**
   * Sets the Organizer creation timestamp.
   *
   * @param int $timestamp
   *   The Organizer creation timestamp.
   *
   * @return \Drupal\event_organizer\Entity\OrganizerInterface
   *   The called Organizer entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Organizer published status indicator.
   *
   * Unpublished Organizer are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Organizer is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Organizer.
   *
   * @param bool $published
   *   TRUE to set this Organizer to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\event_organizer\Entity\OrganizerInterface
   *   The called Organizer entity.
   */
  public function setPublished($published);

  /**
   * Gets the Organizer revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Organizer revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\event_organizer\Entity\OrganizerInterface
   *   The called Organizer entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Organizer revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Organizer revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\event_organizer\Entity\OrganizerInterface
   *   The called Organizer entity.
   */
  public function setRevisionUserId($uid);

}
