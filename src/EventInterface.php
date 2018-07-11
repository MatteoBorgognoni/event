<?php

namespace Drupal\event;

use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\event_content\Entity\EventContentInterface;
use Drupal\event_venue\Entity\VenueInterface;
use Drupal\event_organizer\Entity\OrganizerInterface;
use Drupal\event_ticket\Entity\TicketInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides an interface defining a event entity.
 */
interface EventInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface, RevisionLogInterface, EntityPublishedInterface {

  /**
   * Denotes that the event is not published.
   */
  const NOT_PUBLISHED = 0;

  /**
   * Denotes that the event is published.
   */
  const PUBLISHED = 1;

  /**
   * Denotes that the event is not promoted to the front page.
   */
  const NOT_PROMOTED = 0;

  /**
   * Denotes that the event is promoted to the front page.
   */
  const PROMOTED = 1;

  /**
   * Denotes that the event is not sticky at the top of the page.
   */
  const NOT_STICKY = 0;

  /**
   * Denotes that the event is sticky at the top of the page.
   */
  const STICKY = 1;

  /**
   * Gets the event type.
   *
   * @return string
   *   The event type.
   */
  public function getType();

  /**
   * Gets the event title.
   *
   * @return string
   *   Title of the event.
   */
  public function getTitle();

  /**
   * Sets the event title.
   *
   * @param string $title
   *   The event title.
   *
   * @return \Drupal\event\EventInterface
   *   The called event entity.
   */
  public function setTitle($title);

  /**
   * Gets the event content.
   *
   * @return \Drupal\event_content\Entity\EventContentInterface
   *   Content of the event.
   */
  public function getContent();

  /**
   * Sets the event content.
   *
   * @param \Drupal\event_content\Entity\EventContentInterface
   *   The event content.
   *
   * @return \Drupal\event\EventInterface
   *   The called event entity.
   */
  public function setContent(EventContentInterface $content);

  /**
   * Gets the event content id.
   *
   * @return integer
   *   Content ID of the event.
   */
  public function getContentId();

  /**
   * Sets the event content id.
   *
   * @param integer $content_id
   *   The event content.
   *
   * @return \Drupal\event\EventInterface
   *   The called event entity.
   */
  public function setContentId($content_id);

  /**
   * Gets the event venue.
   *
   * @return \Drupal\event_venue\Entity\VenueInterface
   *   Venue of the event.
   */
  public function getVenue();

  /**
   * Sets the event venue.
   *
   * @param \Drupal\event_venue\Entity\VenueInterface
   *   The event venue.
   *
   * @return \Drupal\event\EventInterface
   *   The called event entity.
   */
  public function setVenue(VenueInterface $venue);

  /**
   * Gets the event venue id.
   *
   * @return integer
   *   Venue ID of the event.
   */
  public function getVenueId();

  /**
   * Sets the event venue id.
   *
   * @param integer $venue_id
   *   The event venue.
   *
   * @return \Drupal\event\EventInterface
   *   The called event entity.
   */
  public function setVenueId($venue_id);

  /**
   * Gets the event tickets.
   *
   * @return array
   *   Tickets of the event.
   */
  public function getTickets();
  
  /**
   * Sets the event tickets.
   *
   * @param array $tickets
   *   The event ticket.
   *
   * @return \Drupal\event\EventInterface
   *   The called event entity.
   */
  public function setTickets($tickets);

  /**
   * Sets the event ticket.
   *
   * @param \Drupal\event_ticket\Entity\TicketInterface
   *   The event ticket.
   *
   * @return \Drupal\event\EventInterface
   *   The called event entity.
   */
  public function setTicket(TicketInterface $ticket);

  /**
   * Gets the event tickets id.
   *
   * @return array
   *   Ticket IDs of the event.
   */
  public function getTicketsId();

  /**
   * Sets the event ticket ids.
   *
   * @param array $ticket_ids
   *   The event tickets.
   *
   * @return \Drupal\event\EventInterface
   *   The called event entity.
   */
  public function setTicketId($ticket_id);

  /**
   * Gets the event organizer.
   *
   * @return \Drupal\event_organizer\Entity\OrganizerInterface
   *   Organizer of the event.
   */
  public function getOrganizer();

  /**
   * Sets the event organizer.
   *
   * @param \Drupal\event_organizer\Entity\OrganizerInterface
   *   The event organizer.
   *
   * @return \Drupal\event\EventInterface
   *   The called event entity.
   */
  public function setOrganizer(OrganizerInterface $organizer);

  /**
   * Gets the event organizer id.
   *
   * @return integer
   *   Organizer ID of the event.
   */
  public function getOrganizerId();

  /**
   * Sets the event organizer id.
   *
   * @param integer $organizer_id
   *   The event organizer.
   *
   * @return \Drupal\event\EventInterface
   *   The called event entity.
   */
  public function setOrganizerId($organizer_id);

  /**
   * Gets the event creation timestamp.
   *
   * @return string
   *   ISO Start date of the event.
   */
  public function getDateStart();

  /**
   * Sets the event start date.
   *
   * @param string $date
   *   The event creation timestamp.
   *
   * @return \Drupal\event\EventInterface
   *   The called event entity.
   */
  public function setDateStart($date);
  
  /**
   * Gets the event creation timestamp.
   *
   * @return string
   *   ISO End date of the event.
   */
  public function getDateEnd();
  
  /**
   * Sets the event end date.
   *
   * @param string $date
   *   The event creation timestamp.
   *
   * @return \Drupal\event\EventInterface
   *   The called event entity.
   */
  public function setDateEnd($date);
  
  /**
   * Returns the event start date visibility status.
   *
   * @return bool
   *   TRUE if the event date start should be hidden.
   */
  public function isStartDateHidden();
  
  /**
   * Returns the event end date visibility status.
   *
   * @return bool
   *   TRUE if the event date end should be hidden.
   */
  public function isEndDateHidden();
  
  /**
   * Returns the event recurring status.
   *
   * @return bool
   *   TRUE if the event is recurring.
   */
  public function isRecurring();
  
  /**
   * Returns the event free status.
   *
   * @return bool
   *   TRUE if the event is free.
   */
  public function isFree();
  
  /**
   * Returns the event online status.
   *
   * @return bool
   *   TRUE if the event is online.
   */
  public function isOnline();
  
  /**
   * Gets the event creation timestamp.
   *
   * @return int
   *   Creation timestamp of the event.
   */
  public function getCreatedTime();

  /**
   * Sets the event creation timestamp.
   *
   * @param int $timestamp
   *   The event creation timestamp.
   *
   * @return \Drupal\event\EventInterface
   *   The called event entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the event promotion status.
   *
   * @return bool
   *   TRUE if the event is promoted.
   */
  public function isPromoted();

  /**
   * Sets the event promoted status.
   *
   * @param bool $promoted
   *   TRUE to set this event to promoted, FALSE to set it to not promoted.
   *
   * @return \Drupal\event\EventInterface
   *   The called event entity.
   */
  public function setPromoted($promoted);

  /**
   * Returns the event sticky status.
   *
   * @return bool
   *   TRUE if the event is sticky.
   */
  public function isSticky();

  /**
   * Sets the event sticky status.
   *
   * @param bool $sticky
   *   TRUE to set this event to sticky, FALSE to set it to not sticky.
   *
   * @return \Drupal\event\EventInterface
   *   The called event entity.
   */
  public function setSticky($sticky);

  /**
   * Gets the event revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the event revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\event\EventInterface
   *   The called event entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the event revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   *
   * @deprecated in Drupal 8.2.0, will be removed before Drupal 9.0.0. Use
   *   \Drupal\Core\Entity\RevisionLogInterface::getRevisionUser() instead.
   */
  public function getRevisionAuthor();

  /**
   * Sets the event revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\event\EventInterface
   *   The called event entity.
   *
   * @deprecated in Drupal 8.2.0, will be removed before Drupal 9.0.0. Use
   *   \Drupal\Core\Entity\RevisionLogInterface::setRevisionUserId() instead.
   */
  public function setRevisionAuthorId($uid);

}
