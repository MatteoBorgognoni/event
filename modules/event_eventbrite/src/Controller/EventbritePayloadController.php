<?php

namespace Drupal\event_eventbrite\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\event\Entity\Event;
use Drupal\event_content\Entity\EventContent;
use Drupal\event_eventbrite\EventEventbriteManager;
use Drupal\media\Entity\Media;
use Drupal\token\Token;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\event\EventManager;
use GuzzleHttp\Client;
use Drupal\event_eventbrite\HttpClient;
use Drupal\image\Entity\ImageStyle;

/**
 * Class EventbritePayloadController.
 */
class EventbritePayloadController extends ControllerBase {

  /**
   * Symfony\Component\HttpFoundation\RequestStack definition.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;
  /**
   * Drupal\Core\Config\ImmutableConfig.
   *
   * @var \\Drupal\Core\Config\ImmutableConfig
   */
  protected $config;
  /**
   * Drupal\Component\Serialization\Json definition.
   *
   * @var \Drupal\Component\Serialization\Json
   */
  protected $json;
  /**
   * Drupal\event\EventManager definition.
   *
   * @var \Drupal\event\EventManager
   */
  protected $eventManager;
  /**
   * Drupal\event_eventbrite\EventEventbriteManager definition.
   *
   * @var \Drupal\event_eventbrite\EventEventbriteManager
   */
  protected $eventbriteManager;
  /**
   * Drupal\event_eventbrite\EventbriteParser definition.
   *
   * @var \Drupal\event_eventbrite\EventbriteParser
   */
  protected $eventbriteParser;
  /**
   * Drupal\event_eventbrite\HttpClient definition.
   *
   * @var \Drupal\event_eventbrite\HttpClient;
   */
  protected $client;

  /**
   * @var resource|string
   */
  protected $payload;

  /**
   * @var null|\Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  protected $eventExpansions;

  /**
   * Constructs a new EventbritePayloadController object.
   */
  public function __construct(
    RequestStack $request_stack,
    ConfigFactoryInterface $config_factory,
    Json $serialization_json,
    EntityTypeManagerInterface $entity_type_manager,
    EventManager $event_manager,
    EventEventbriteManager $event_eventbrite_manager
  ) {
    $this->requestStack = $request_stack;
    $this->config = $config_factory->get('event_eventbrite.settings');
    $this->json = $serialization_json;
    $this->entityTypeManager = $entity_type_manager;
    $this->eventManager = $event_manager;
    $this->eventbriteManager = $event_eventbrite_manager;
    $this->client = $this->eventbriteManager->client;
    $this->request = $this->requestStack->getCurrentRequest();
    $this->payload = $this->json->decode($this->request->getContent());
    $this->eventExpansions = ['organizer', 'venue', 'format', 'refund_policy', 'ticket_classes'];
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('config.factory'),
      $container->get('serialization.json'),
      $container->get('entity_type.manager'),
      $container->get('event.manager'),
      $container->get('event_eventbrite.manager')
    );
  }

  /**
   * Parse.
   *
   * @return mixed
   *   Return Hello string.
   */
  public function parsePayload() {

    if(!$this->isValidPayload()) {
      $this->eventManager->log('error', 'Payload not valid');
      //throw new AccessDeniedException();
    }

    $action = $this->getAction();

    $eventData = $this->getEventData();

    if($eventData) {
      $this->process($eventData, $action);
    }
    else {
      $this->eventManager->log('error', '<pre>' . print_r($eventData, true) . '</pre>');
    }

    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: parse'),
    ];
  }

  public function isValidPayload() {
    if(!isset($this->payload['config']) && !isset($this->payload['config']['action']) && !isset($this->payload['api_url'])) {
      $this->eventManager->log('error', 'Something wrong with the payload: ' . '<pre>' . print_r($this->payload, true) . '</pre>');
      return false;
    }

    return TRUE;
  }

  public function getAction() {

    $mode = $this->config->get('mode');
    if($mode == 'test') {
      $action = $this->config->get('action');
    }
    else {
      $action = explode('.', $this->payload['config']['action']);
      $action = $action[1];
    }

    return $action;
  }

  public function getEventData() {

    $mode = $this->config->get('mode');

    if(!$this->isValidPayload() && $mode == 'test') {
      $api_url = $this->config->get('test_event');
    }
    else {
      $api_url = $this->payload['api_url'];
    }

    if($api_url) {
      $path = str_replace('https://www.eventbriteapi.com/v3', '', $api_url);
      $event_data = $this->client->get($path, $this->eventExpansions);
      $bag = new ParameterBag($event_data);

      if($bag->has('error')) {
        return NULL;
      }

      return $bag;
    }
    else {
      return NULL;
    }
  }

  public function process(ParameterBag $eventData, $op) {
    $events = [];
    $eventbrite_id = (int) $eventData->get('id');
    $event_exists = $this->eventbriteManager->loadEntityByEventbriteId('event_content', $eventbrite_id);

    if($op == 'create' && $event_exists) {
      $op = 'update';
    }

    if($op == 'update' && !$event_exists) {
      $op = 'create';
    }

    ///** @var \Symfony\Component\HttpFoundation\ParameterBag $values */
    $values = $this->eventbriteManager->getParsedValues($eventData, $op);

    if($eventData->getBoolean('is_series')) {
      $seriesData = $this->client->get('/series/' . $eventbrite_id . '/events', $this->eventExpansions);
      foreach ($seriesData['events'] as $key => $seriesEventData) {
        $bag = new ParameterBag($seriesEventData);
        /** @var \Symfony\Component\HttpFoundation\ParameterBag $eventValues */
        $eventValues = $this->eventbriteManager->getParsedValues($bag, $op);
        $events[$key] = $this->processSingle($bag, $eventValues, $op);
      }
    }
    else {
      $events[0] = $this->processSingle($eventData, $values, $op);
    }

    /** @var \Drupal\event_content\Entity\EventContentInterface $event_content */
    $this->processEventContent($values, $events, $op);
    $this->eventManager->log('info', 'Eventbrite webhook action "@action" for the event "@event"', ['@action' => $op, '@event' => $eventData->get('name')['text']]);

  }

  public function processSingle(ParameterBag $eventData, ParameterBag $values, $op) {

    switch ($op) {

      case 'create':
      case 'update':

        if($values->has('event')) {
          /** @var \Drupal\event\Entity\Event $event */
          $event = $this->processEntity('event', $values->get('event'));
        }

        return $event;
        break;

      case 'publish':
        $eventbrite_id = $eventData->get('id');
        /** @var Event $event */
        $event = $this->eventbriteManager->loadEntityByEventbriteId('event', $eventbrite_id);
        $event->set('moderation_state', 'published');
        $event->setPublished();
        $event->save();
        return $event;
        break;

      case 'unpublish':
        $eventbrite_id = $eventData->get('id');
        /** @var Event $event */
        $event = $this->eventbriteManager->loadEntityByEventbriteId('event', $eventbrite_id);
        $event->set('moderation_state', 'draft');
        $event->setUnpublished();
        $event->save();
        return $event;
        break;
    }
  }

  public function processEntity($entity_type, ParameterBag $bag) {
    if($bag->has('eventbrite_id')) {
      $eventbrite_id = $bag->get('eventbrite_id');
      if($e = $this->eventbriteManager->loadEntityByEventbriteId($entity_type, $eventbrite_id)) {
        /** @var \Drupal\Core\Entity\EntityInterface $entity */
        $entity = $this->eventManager->updateEntity($e, $bag);
      }
      else {
        /** @var \Drupal\Core\Entity\EntityInterface $entity */
        $entity = $this->eventManager->createEntity($entity_type, $bag, TRUE);
      }
      return $entity;
    }
    else {
      $this->eventManager->log('error', 'An entity of type "' . $entity_type . '" has tried to save without an eventbrite ID');
    }
  }

  public function processEventContent(ParameterBag $values, $events, $op) {
    /** @var ParameterBag $event_content_values */
    $event_content_values = $values->get('event_content');
    switch ($op) {
      case 'create':
        /** @var \Drupal\event_content\Entity\EventContent $event_content */
        $event_content = $this->eventManager->createEntity('event_content', $event_content_values, TRUE);

        /** @var ParameterBag $media_values */
        $media_values = $values->get('media');
        $media = $this->processMediaEntity($media_values, $event_content->label());

        $event_content->set('events', $events);
        $event_content->set('image_main', $media);
        $event_content->set('image_summary', $media);

        if($values->has('venue')) {
          /** @var \Drupal\event_venue\Entity\Venue $venue */
          $venue = $this->processEntity('venue', $values->get('venue'));
        }
        if($values->has('organizer')) {
          /** @var \Drupal\event_organizer\Entity\Organizer $organizer */
          $organizer = $this->processEntity('organizer', $values->get('organizer'));
        }
        if($values->has('tickets')) {
          $tickets = [];
          foreach ($values->get('tickets') as $ticketValues) {
            $tickets[] = $this->processEntity('ticket', $ticketValues);
          }
        }
        $event_content->save();

        /** @var \Drupal\event\EventInterface $event */
        foreach ($events as $event) {
          $event->set('content', $event_content);
          $event->set('venue', $venue);
          $event->set('tickets', $tickets);
          $event->set('organizer', $organizer);
          $event->save();
        }

        return $event_content;
        break;

      case 'update':
        $eventbrite_id = $event_content_values->get('eventbrite_id');
        /** @var \Drupal\event_content\Entity\EventContentInterface $event_content */
        $old_event_content = $this->eventbriteManager->loadEntityByEventbriteId('event_content', $eventbrite_id);
        $event_content = $this->eventManager->updateEntity($old_event_content, $values->get('event_content'));

        foreach ($events as $event) {
          $eventbrite_id = $event->get('eventbrite_id')->value;
          if (!$this->eventbriteManager->loadEntityByEventbriteId('event', $eventbrite_id)) {
            $event_content->get('events')->appendItem($event);
          }
        }

        $event_content->save();
        return $event_content;
        break;

      case 'publish':
        /** @var \Drupal\event\EventInterface $event */
        $event = $events[0];
        /** @var \Drupal\event_content\Entity\EventContentInterface $event_content */
        $event_content = $event->getContent();
        $event_content = $this->eventManager->publishEntity($event_content);
        break;

      case 'unpublish':
        /** @var \Drupal\event\EventInterface $event */
        $event = $events[0];
        /** @var \Drupal\event_content\Entity\EventContentInterface $event_content */
        $event_content = $event->getContent();
        $event_content = $this->eventManager->unpublishEntity($event_content);
        break;

    }
  }


  public function processMediaEntity(ParameterBag $bag, $title) {

    if($bag->has('field_image')) {
      $bundle = $bag->get('bundle');
      $url = $bag->get('field_image');
      $field_config = $this->eventManager->configFactory->get('field.field.media.' . $bundle . '.field_image')->getRawData();
      $directory = \Drupal::token()->replace($field_config['settings']['file_directory']);
      $filename = Html::cleanCssIdentifier($title);
      $destination = file_default_scheme() . '://'. $filename . '.jpg';
      $managed = TRUE;
      $replace = FILE_EXISTS_REPLACE;
      /** @var \Drupal\file\Entity\File $file */
      $file = system_retrieve_file($url, $destination, $managed, $replace);

      if($file) {
        $file->setPermanent();

        $image = \Drupal::service('image.factory')->get($file->getFileUri());
        /** @var \Drupal\Core\Image\Image $image */
        if ($image->isValid()) {
          $styles = ImageStyle::loadMultiple();
          $image_uri = $file->getFileUri();
          /** @var \Drupal\image\Entity\ImageStyle $style */
          foreach ($styles as $style) {
            $destination = $style->buildUri($image_uri);
            $style->createDerivative($image_uri, $destination);
          }
        }

        /** @var \Drupal\media\MediaInterface $media */
        $media = Media::create([
          'bundle' => 'image',
          'uid' => 1,
          'langcode' => \Drupal::languageManager()->getDefaultLanguage()->getId(),
          'status' => 1,
          'field_image' => [
            'target_id' => $file->id(),
            'alt' => t($title),
            'title' => t($title),
          ],
        ]);
        $media->save();
        return $media;
      }
      else {
        return NULL;
      }
    }
  }

}
