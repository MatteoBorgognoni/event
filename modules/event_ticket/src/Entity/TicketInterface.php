<?php

namespace Drupal\event_ticket\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Ticket entities.
 *
 * @ingroup event_ticket
 */
interface TicketInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Ticket name.
   *
   * @return string
   *   Name of the Ticket.
   */
  public function getName();

  /**
   * Sets the Ticket name.
   *
   * @param string $name
   *   The Ticket name.
   *
   * @return \Drupal\event_ticket\Entity\TicketInterface
   *   The called Ticket entity.
   */
  public function setName($name);
  
  /**
   * @param bool $raw
   * @return mixed
   */
  public function getValue($raw = FALSE);
  
  /**
   * @return mixed
   */
  public function getFormattedValue();
  
  /**
   * Gets the Ticket creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Ticket.
   */
  public function getCreatedTime();

  /**
   * Sets the Ticket creation timestamp.
   *
   * @param int $timestamp
   *   The Ticket creation timestamp.
   *
   * @return \Drupal\event_ticket\Entity\TicketInterface
   *   The called Ticket entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Ticket published status indicator.
   *
   * Unpublished Ticket are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Ticket is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Ticket.
   *
   * @param bool $published
   *   TRUE to set this Ticket to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\event_ticket\Entity\TicketInterface
   *   The called Ticket entity.
   */
  public function setPublished($published);

  /**
   * Gets the Ticket revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Ticket revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\event_ticket\Entity\TicketInterface
   *   The called Ticket entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Ticket revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Ticket revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\event_ticket\Entity\TicketInterface
   *   The called Ticket entity.
   */
  public function setRevisionUserId($uid);

}
