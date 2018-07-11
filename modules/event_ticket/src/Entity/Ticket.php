<?php

namespace Drupal\event_ticket\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\event_ticket\Plugin\Field\TicketFormattedValue;
use Drupal\user\UserInterface;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Defines the Ticket entity.
 *
 * @ingroup event_ticket
 *
 * @ContentEntityType(
 *   id = "ticket",
 *   label = @Translation("Ticket"),
 *   bundle_label = @Translation("Ticket type"),
 *   handlers = {
 *     "storage" = "Drupal\event_ticket\TicketStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\event_ticket\TicketListBuilder",
 *     "views_data" = "Drupal\event_ticket\Entity\TicketViewsData",
 *     "translation" = "Drupal\event_ticket\TicketTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\event_ticket\Form\TicketForm",
 *       "add" = "Drupal\event_ticket\Form\TicketForm",
 *       "edit" = "Drupal\event_ticket\Form\TicketForm",
 *       "delete" = "Drupal\event_ticket\Form\TicketDeleteForm",
 *     },
 *     "access" = "Drupal\event_ticket\TicketAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\event_ticket\TicketHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "ticket",
 *   data_table = "ticket_field_data",
 *   revision_table = "ticket_revision",
 *   revision_data_table = "ticket_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer ticket entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/events/ticket/{ticket}",
 *     "add-page" = "/admin/events/ticket/add",
 *     "add-form" = "/admin/events/ticket/add/{ticket_type}",
 *     "edit-form" = "/admin/events/ticket/{ticket}/edit",
 *     "delete-form" = "/admin/events/ticket/{ticket}/delete",
 *     "version-history" = "/admin/events/ticket/{ticket}/revisions",
 *     "revision" = "/admin/events/ticket/{ticket}/revisions/{ticket_revision}/view",
 *     "revision_revert" = "/admin/events/ticket/{ticket}/revisions/{ticket_revision}/revert",
 *     "revision_delete" = "/admin/events/ticket/{ticket}/revisions/{ticket_revision}/delete",
 *     "translation_revert" = "/admin/events/ticket/{ticket}/revisions/{ticket_revision}/revert/{langcode}",
 *     "collection" = "/admin/events/ticket",
 *   },
 *   bundle_entity_type = "ticket_type",
 *   field_ui_base_route = "entity.ticket_type.edit_form"
 * )
 */
class Ticket extends RevisionableContentEntityBase implements TicketInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    if ($rel === 'revision_revert' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    elseif ($rel === 'revision_delete' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }

    return $uri_route_parameters;
  }

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

    // If no revision author has been set explicitly, make the ticket owner the
    // revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getValue($raw = TRUE) {
    switch ($raw) {
      case TRUE:
        return (float) $this->get('value')->value;
        break;
      case FALSE:
        return (float) $this->get('value')->value / 100;
        break;
    }
  }
  
  /**
   * {@inheritdoc}
   */
  public function getFormattedValue() {
    //TODO internationalization
    
    $amount = (float) $this->getValue() / 100;
    
    setlocale(LC_MONETARY, 'en_GB.UTF-8');
    $amount = money_format('%n', $amount);
    return $amount;
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
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? TRUE : FALSE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Ticket entity.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
  
    $fields['quantity'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Ticket quantity'))
      ->setDescription(t('Number of tickets available'))
      ->setRequired(TRUE)
      ->setRevisionable(TRUE)
      ->setSettings(array(
        'min' => 0,
        'text_processing' => 0,
      ))
      ->setDisplayOptions('view', array(
        'type' => 'string',
        'weight' => -9,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'number',
        'weight' => -9,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
  
    $fields['quantity_sold'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Ticket quantity sold'))
      ->setDescription(t('Number of tickets sold'))
      ->setRequired(TRUE)
      ->setRevisionable(TRUE)
      ->setSettings(array(
        'min' => 0,
        'text_processing' => 0,
      ))
      ->setDisplayOptions('view', array(
        'type' => 'string',
        'weight' => -9,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'number',
        'weight' => -9,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
  
    $fields['currency'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Currency'))
      ->setDescription(t('Currency code'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 8,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', FALSE)
      ->setRequired(TRUE);

    $fields['value'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Ticket value'))
      ->setDescription(t('Raw value of the ticket'))
      ->setRevisionable(TRUE)
      ->setSettings(array(
        'min' => 0,
        'text_processing' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'number',
        'weight' => -9,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['formatted_value'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Ticket formatted value'))
      ->setDescription(t('Final price of the ticket'))
      ->setComputed(TRUE)
      ->setClass(TicketFormattedValue::class)
      ->setSettings(array(
        'min' => 0,
        'text_processing' => 0,
        'field_name' => 'value'
      ))
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'weight' => -9,
      ))
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', TRUE);
  
  
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
        'weight' => 16,
      ])
      ->setDisplayConfigurable('form', FALSE);
  
//    $fields['sale_status'] = BaseFieldDefinition::create('list_string')
//      ->setLabel(t('Sale status'))
//      ->setCardinality(1)
//      ->setDefaultValue('AVAILABLE')
//      ->setSettings(array(
//        'allowed_values' => [
//          'AVAILABLE' => 'Available',
//          'SOLD_OUT' => 'Sold out',
//        ],
//      ))
//      ->setDisplayOptions('view', [
//        'label' => 'above',
//        'type' => 'string',
//        'weight' => -9,
//      ])
//      ->setDisplayOptions('form', array(
//        'type' => 'options_buttons',
//        'weight' => -9,
//      ))
//      ->setDisplayConfigurable('form', TRUE)
//      ->setDisplayConfigurable('view', TRUE);
  
    $fields['sales_start'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Sales start date'))
      ->setDescription(t('The starting date for the ticket sale.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'datetime_default',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_default',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
  
    $fields['sales_end'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Sales end date'))
      ->setDescription(t('The ending date for the ticket sale.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'datetime_default',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_default',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
  
    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Ticket entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', FALSE);
    
    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Ticket is published.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

}
