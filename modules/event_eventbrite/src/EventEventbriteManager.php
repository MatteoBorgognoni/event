<?php

namespace Drupal\event_eventbrite;

use Drupal\event_eventbrite\EventbriteClient;
use Drupal\event_eventbrite\EventbriteParser;
use Drupal\event\EventManager;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class EventEventbriteManager.
 */
class EventEventbriteManager {
  /**
   * Drupal\event_eventbrite\HttpClient definition.
   *
   * @var \Drupal\event_eventbrite\HttpClient
   */
  public $client;
  /**
   * Drupal\event_eventbrite\EventbriteParser definition.
   *
   * @var \Drupal\event_eventbrite\EventbriteParser
   */
  public $eventEventbriteParser;
  /**
   * Drupal\event\EventManager definition.
   *
   * @var \Drupal\event\EventManager
   */
  protected $eventManager;
  /**
   * Constructs a new EventEventbriteManager object.
   */
  public function __construct(EventbriteClient $eventbrite, EventbriteParser $event_eventbrite_parser, EventManager $event_manager) {
    $this->eventEventbriteParser = $event_eventbrite_parser;
    $this->eventManager = $event_manager;
    $this->client = $eventbrite->client();
  }

  public function getFieldMap() {
    return $this->eventManager->configFactory->get('event_eventbrite.fieldmap');
  }

  public function getParsedValues(ParameterBag $eventData, $op) {

    $eventValues = $this->eventEventbriteParser->getEntityValues('event', $eventData, 'standard', $op);

    $eventContentValues = $this->eventEventbriteParser->getEntityValues('event_content', $eventData, 'event_content', $op);

    $venueValues = $this->eventEventbriteParser->getEntityValues('venue', $eventData, 'standard', $op);

    $ticketValues = [];
    foreach ($eventData->get('ticket_classes') as $ticket_class) {
      $ticketData = new ParameterBag($ticket_class);
      $ticketValues[] = $this->eventEventbriteParser->getEntityValues('ticket', $ticketData, 'standard', $op);
    }

    $mediaValues = $this->eventEventbriteParser->getEntityValues('media', $eventData, 'image', $op);

    $organizerValues = $this->eventEventbriteParser->getEntityValues('organizer', $eventData, 'standard', $op);

    return new ParameterBag([
      'event' => $eventValues,
      'venue' => $venueValues,
      'event_content' => $eventContentValues,
      'tickets' => $ticketValues,
      'media' => $mediaValues,
      'organizer' => $organizerValues,
    ]);
  }

  public function loadEntityByEventbriteId($entity_type, $eventbrite_id) {
    $query = $this->eventManager->getEntityQuery($entity_type);
    $query->condition('eventbrite_id', $eventbrite_id);
    $entity_ids = $query->execute();
    $entity_id = (int) reset($entity_ids);
    $entity = $entity_id ? $this->eventManager->loadEntity($entity_type, $entity_id) : NULL;
    return $entity;
  }
  
}
