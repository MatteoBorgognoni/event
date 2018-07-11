<?php

namespace Drupal\event\Entity;

use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\event\EventInterface;
use Drupal\event\Plugin\Field\EventContentImage;
use Drupal\event\Plugin\Field\EventContentParagraph;
use Drupal\event\Plugin\Field\EventContentText;
use Drupal\event\Plugin\Field\EventRegistrationButton;
use Drupal\event\Plugin\Field\GeolocationFieldReference;
use Drupal\user\UserInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\event_content\Entity\EventContentInterface;
use Drupal\event_venue\Entity\VenueInterface;
use Drupal\event_organizer\Entity\OrganizerInterface;
use Drupal\event_ticket\Entity\TicketInterface;
use Drupal\event\Plugin\Field\EntityFieldReference;

/**
 * Defines the event entity class.
 *
 * @ContentEntityType(
 *   id = "event",
 *   label = @Translation("Events"),
 *   label_collection = @Translation("Events"),
 *   label_singular = @Translation("event"),
 *   label_plural = @Translation("event"),
 *   label_count = @PluralTranslation(
 *     singular = "@count event",
 *     plural = "@count events"
 *   ),
 *   bundle_label = @Translation("Event type"),
 *   handlers = {
 *     "storage" = "Drupal\event\EventStorage",
 *     "storage_schema" = "Drupal\event\EventStorageSchema",
 *     "view_builder" = "Drupal\event\EventViewBuilder",
 *     "access" = "Drupal\event\EventAccessControlHandler",
 *     "views_data" = "Drupal\event\EventViewsData",
 *     "form" = {
 *       "default" = "Drupal\event\EventForm",
 *       "delete" = "Drupal\event\Form\EventDeleteForm",
 *       "edit" = "Drupal\event\EventForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\event\Entity\EventRouteProvider",
 *     },
 *     "list_builder" = "Drupal\event\EventListBuilder",
 *     "translation" = "Drupal\event\EventTranslationHandler"
 *   },
 *   base_table = "event",
 *   data_table = "event_field_data",
 *   revision_table = "event_revision",
 *   revision_data_table = "event_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   list_cache_contexts = { "user.event_grants:view" },
 *   entity_keys = {
 *     "id" = "eid",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "label" = "title",
 *     "langcode" = "langcode",
 *     "uuid" = "uuid",
 *     "status" = "status",
 *     "published" = "status",
 *     "uid" = "uid",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_uid",
 *     "revision_created" = "revision_timestamp",
 *     "revision_log_message" = "revision_log"
 *   },
 *   bundle_entity_type = "event_type",
 *   field_ui_base_route = "entity.event_type.edit_form",
 *   common_reference_target = TRUE,
 *   permission_granularity = "bundle",
 *   links = {
 *     "canonical" = "/event/{event}",
 *     "delete-form" = "/event/{event}/delete",
 *     "edit-form" = "/event/{event}/edit",
 *     "version-history" = "/event/{event}/revisions",
 *     "revision" = "/event/{event}/revisions/{event_revision}/view",
 *     "create" = "/event",
 *   }
 * )
 */
class Event extends EditorialContentEntityBase implements EventInterface {

  /**
   * Whether the event is being previewed or not.
   *
   * The variable is set to public as it will give a considerable performance
   * improvement. See https://www.drupal.org/event/2498919.
   *
   * @var true|null
   *   TRUE if the event is being previewed and NULL if it is not.
   */
  public $in_preview = NULL;

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    // If no revision author has been set explicitly, make the event owner the
    // revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preSaveRevision(EntityStorageInterface $storage, \stdClass $record) {
    parent::preSaveRevision($storage, $record);

    if (!$this->isNewRevision() && isset($this->original) && (!isset($record->revision_log) || $record->revision_log === '')) {
      // If we are updating an existing event without adding a new revision, we
      // need to make sure $entity->revision_log is reset whenever it is empty.
      // Therefore, this code allows us to avoid clobbering an existing log
      // entry with an empty one.
      $record->revision_log = $this->original->revision_log->value;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    // Update the event access table for this event, but only if it is the
    // default revision. There's no need to delete existing records if the event
    // is new.
    if ($this->isDefaultRevision()) {
      /** @var \Drupal\event\EventAccessControlHandlerInterface $access_control_handler */
      $access_control_handler = \Drupal::entityManager()->getAccessControlHandler('event');
      $grants = $access_control_handler->acquireGrants($this);
      \Drupal::service('event.grant_storage')->write($this, $grants, NULL, $update);
    }

    // Reindex the event when it is updated. The event is automatically indexed
    // when it is added, simply by being added to the event table.
    if ($update) {
      event_reindex_event_search($this->id());
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    parent::preDelete($storage, $entities);

    // Ensure that all events deleted are removed from the search index.
    if (\Drupal::moduleHandler()->moduleExists('search')) {
      foreach ($entities as $entity) {
        search_index_clear('event_search', $entity->eid->value);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $events) {
    parent::postDelete($storage, $events);
    \Drupal::service('event.grant_storage')->deleteEventRecords(array_keys($events));
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->bundle();
  }

  /**
   * {@inheritdoc}
   */
  public function access($operation = 'view', AccountInterface $account = NULL, $return_as_object = FALSE) {
    // This override exists to set the operation to the default value "view".
    return parent::access($operation, $account, $return_as_object);
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->get('title')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($title) {
    $this->set('title', $title);
    return $this;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getContent() {
    return $this->get('content')->entity;
  }
  
  /**
   * {@inheritdoc}
   */
  public function setContent(EventContentInterface $content) {
    $this->set('content', $content->id());
    return $this;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getContentId() {
    return $this->get('content')->value;
  }
  
  /**
   * {@inheritdoc}
   */
  public function setContentId($content_id) {
    $this->set('content', $content_id);
    return $this;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getVenue() {
    return $this->get('venue')->entity;
  }
  
  /**
   * {@inheritdoc}
   */
  public function setVenue(VenueInterface $venue) {
    $this->set('venue', $venue->id());
    return $this;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getVenueId() {
    return $this->get('venue')->value;
  }
  
  /**
   * {@inheritdoc}
   */
  public function setVenueId($venue_id) {
    $this->set('venue', $venue_id);
    return $this;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getTickets() {
    $tickets = $this->get('tickets')->getValue();
    return $tickets;
  }
  
  /**
   * {@inheritdoc}
   */
  public function setTickets($tickets) {
    foreach ($tickets as $ticket) {
      if($ticket instanceof TicketInterface) {
        $this->setTicket($ticket);
      }
    }
    return $this;
  }
  
  /**
   * {@inheritdoc}
   */
  public function setTicket(TicketInterface $ticket) {
    $this->get('tickets')->appendItem($ticket);
    return $this;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getTicketsId() {
    $tickets = $this->get('tickets')->getValue();
    return $tickets;
  }
  
  /**
   * {@inheritdoc}
   */
  public function setTicketId($ticket_id) {
    $this->get('tickets')->appendItem($ticket_id);
    return $this;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getOrganizer() {
    return $this->get('organizer')->entity;
  }
  
  /**
   * {@inheritdoc}
   */
  public function setOrganizer(OrganizerInterface $organizer) {
    $this->set('organizer', $organizer->id());
    return $this;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getOrganizerId() {
    return $this->get('organizer')->value;
  }
  
  /**
   * {@inheritdoc}
   */
  public function setOrganizerId($organizer_id) {
    $this->set('organizer', $organizer_id);
    return $this;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getDateStart() {
    return $this->get('date_start')->getValue();
  }
  
  /**
   * {@inheritdoc}
   */
  public function setDateStart($date) {
    $this->set('date_start', $date);
    return $this;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getDateEnd() {
    return $this->get('date_end')->getValue();
  }
  
  /**
   * {@inheritdoc}
   */
  public function setDateEnd($date) {
    $this->set('date_end', $date);
    return $this;
  }
  
  /**
   * {@inheritdoc}
   */
  public function isStartDateHidden() {
    return (bool) $this->get('hide_date_start')->value;
  }
  
  /**
   * {@inheritdoc}
   */
  public function isEndDateHidden() {
    return (bool) $this->get('hide_date_end')->value;
  }
  
  /**
   * {@inheritdoc}
   */
  public function isRecurring() {
    return (bool) $this->get('is_recurring')->value;
  }
  
  /**
   * {@inheritdoc}
   */
  public function isFree()  {
    return (bool) $this->get('is_free')->value;
  }
  
  /**
   * {@inheritdoc}
   */
  public function isOnline() {
    return (bool) $this->get('is_online')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }


  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPromoted() {
    return (bool) $this->get('promote')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setPromoted($promoted) {
    $this->set('promote', $promoted ? EventInterface::PROMOTED : EventInterface::NOT_PROMOTED);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isSticky() {
    return (bool) $this->get('sticky')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setSticky($sticky) {
    $this->set('sticky', $sticky ? EventInterface::STICKY : EventInterface::NOT_STICKY);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->getEntityKey('uid');
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRevisionAuthor() {
    return $this->getRevisionUser();
  }

  /**
   * {@inheritdoc}
   */
  public function setRevisionAuthorId($uid) {
    $this->setRevisionUserId($uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -50,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -50,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);


    $fields['content'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Event content'))
      ->setDescription(t('The common content to attach to the event(s).'))
      ->setRevisionable(TRUE)
      ->setCardinality(1)
      ->setSetting('target_type', 'event_content')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => -47,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['main_image'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Main image'))
      ->setCardinality(1)
      ->setTargetEntityTypeId('media')
      ->setSetting('field_name', 'image_main')
      ->setSetting('handler', 'default:media')
      ->setSetting('target_type', 'media')
      ->setSetting('handler_settings', ['target_bundles' => ['image' => 'image']])
      ->setComputed(TRUE)
      ->setClass(EventContentImage::class)
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'weight' => -50,
      ]);

    $fields['text_intro'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Introductory text'))
      ->setCardinality(1)
      ->setTranslatable(TRUE)
      ->setComputed(TRUE)
      ->setSetting('field_name', 'text_intro')
      ->setClass(EventContentText::class)
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'text_long',
        'label' => 'hidden',
        'weight' => -5,
      ]);

    $fields['summary_image'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Summary image'))
      ->setCardinality(1)
      ->setTargetEntityTypeId('media')
      ->setSetting('field_name', 'image_summary')
      ->setSetting('handler', 'default:media')
      ->setSetting('target_type', 'media')
      ->setSetting('handler_settings', ['target_bundles' => ['image' => 'image']])
      ->setComputed(TRUE)
      ->setClass(EventContentImage::class)
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'weight' => -5,
      ]);

    $fields['text_summary'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Summary text'))
      ->setCardinality(1)
      ->setTranslatable(TRUE)
      ->setComputed(TRUE)
      ->setSetting('field_name', 'text_summary')
      ->setClass(EventContentText::class)
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'text_long',
        'label' => 'hidden',
        'weight' => -5,
      ]);


    $fields['venue'] = BaseFieldDefinition::create('entity_reference_revisions')
      ->setLabel(t('Location'))
      ->setDescription(t('The location of the event(s).'))
      ->setRevisionable(TRUE)
      ->setCardinality(1)
      ->setSetting('target_type', 'venue')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_revisions_autocomplete',
        'weight' => -44,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', FALSE);


    $fields['address'] = BaseFieldDefinition::create('generic_address')
      ->setLabel(t('Address'))
      ->setTranslatable(FALSE)
      ->setClass(EntityFieldReference::class)
      ->setComputed(TRUE)
      ->setCardinality(1)
      ->setSetting('entity_type', 'venue')
      ->setSetting('field_name', 'address')
      ->setDisplayOptions('view', array(
        'type' => 'generic_address_formatted',
        'weight' => -3,
        'label' => 'hidden',
      ))
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['geolocation'] = BaseFieldDefinition::create('geolocation')
      ->setLabel(t('Geolocation'))
      ->setTranslatable(FALSE)
      ->setClass(GeolocationFieldReference::class)
      ->setComputed(TRUE)
      ->setCardinality(1)
      ->setSetting('entity_type', 'venue')
      ->setSetting('field_name', 'geolocation')
      ->setDisplayOptions('view', array(
        'type' => 'geolocation_map',
        'weight' => -3,
        'label' => 'hidden',
      ))
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', TRUE);


    $fields['tickets'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Tickets'))
      ->setDescription(t('The tickets for the event.'))
      ->setRevisionable(TRUE)
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'ticket')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'entity_reference_entity_view',
        'weight' => -47,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => -40,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['organizer'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Organiser'))
      ->setDescription(t('The organiser of the event(s).'))
      ->setRevisionable(TRUE)
      ->setCardinality(1)
      ->setSetting('target_type', 'organizer')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'entity_reference_entity_view',
        'weight' => -47,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => -37,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['registration_button'] = BaseFieldDefinition::create('registration_button')
      ->setLabel(t('Registration button'))
      ->setRevisionable(TRUE)
      ->setComputed(TRUE)
      ->setSetting('entity_type', 'event_content')
      ->setSetting('field_name', 'button_text')
      ->setClass(EventRegistrationButton::class)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'registration_button_default',
        'weight' => -1,
      ])
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['date_start'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Start date'))
      ->setDescription(t('The starting time for the event.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'datetime_event',
        'weight' => -45,
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_default',
        'weight' => -34,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDefaultValue(DrupalDateTime::createFromTimestamp(time()));
  
    $fields['hide_date_start'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Hide start date'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => -33,
      ])
      ->setDisplayConfigurable('form', TRUE);
    
    $fields['date_end'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('End date'))
      ->setDescription(t('The ending time for the event.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'datetime_event',
        'weight' => -44,
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_default',
        'weight' => -32,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDefaultValue(DrupalDateTime::createFromTimestamp(time()));

    $fields['hide_date_end'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Hide end date'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => -31,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['slices_content'] = BaseFieldDefinition::create('entity_reference_revisions')
      ->setLabel(t('Content slices'))
      ->setCardinality(-1)
      ->setComputed(TRUE)
      ->setClass(EventContentParagraph::class)
      ->setTargetEntityTypeId('paragraph')
      ->setSetting('field_name', 'field_slices_content')
      ->setSetting('handler', 'default:paragraph')
      ->setSetting('target_type', 'paragraph')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'weight' => -50,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['slices_related'] = BaseFieldDefinition::create('entity_reference_revisions')
      ->setLabel(t('Related content'))
      ->setCardinality(-1)
      ->setComputed(TRUE)
      ->setClass(EventContentParagraph::class)
      ->setTargetEntityTypeId('paragraph')
      ->setSetting('field_name', 'field_slices_related')
      ->setSetting('handler', 'default:paragraph')
      ->setSetting('target_type', 'paragraph')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'weight' => -50,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['is_recurring'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Recurring'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => -29,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['is_free'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Free event'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => -27,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['is_online'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Online event'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => -25,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['status']
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => 120,
      ])
      ->setDisplayConfigurable('form', TRUE);
  
  
    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The username of the event author.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback('Drupal\event\Entity\Event::getCurrentUserId')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', FALSE);
    
    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the event was created.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the event was last edited.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    $fields['promote'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Promoted to front page'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => 15,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['sticky'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Sticky at top of lists'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => 16,
      ])
      ->setDisplayConfigurable('form', TRUE);

    return $fields;
  }

  /**
   * Default value callback for 'uid' base field definition.
   *
   * @see ::baseFieldDefinitions()
   *
   * @return array
   *   An array of default values.
   */
  public static function getCurrentUserId() {
    return [\Drupal::currentUser()->id()];
  }

}
