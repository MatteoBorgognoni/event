<?php

namespace Drupal\event_venue\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Venue entities.
 *
 * @ingroup event_venue
 */
interface VenueInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Venue name.
   *
   * @return string
   *   Name of the Venue.
   */
  public function getName();

  /**
   * Sets the Venue name.
   *
   * @param string $name
   *   The Venue name.
   *
   * @return \Drupal\event_venue\Entity\VenueInterface
   *   The called Venue entity.
   */
  public function setName($name);

  /**
   * Gets the Venue creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Venue.
   */
  public function getCreatedTime();

  /**
   * Sets the Venue creation timestamp.
   *
   * @param int $timestamp
   *   The Venue creation timestamp.
   *
   * @return \Drupal\event_venue\Entity\VenueInterface
   *   The called Venue entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Venue published status indicator.
   *
   * Unpublished Venue are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Venue is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Venue.
   *
   * @param bool $published
   *   TRUE to set this Venue to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\event_venue\Entity\VenueInterface
   *   The called Venue entity.
   */
  public function setPublished($published);

  /**
   * Gets the Venue revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Venue revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\event_venue\Entity\VenueInterface
   *   The called Venue entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Venue revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Venue revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\event_venue\Entity\VenueInterface
   *   The called Venue entity.
   */
  public function setRevisionUserId($uid);

}
