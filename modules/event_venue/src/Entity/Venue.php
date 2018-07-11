<?php

namespace Drupal\event_venue\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Venue entity.
 *
 * @ingroup event_venue
 *
 * @ContentEntityType(
 *   id = "venue",
 *   label = @Translation("Venue"),
 *   bundle_label = @Translation("Venue type"),
 *   handlers = {
 *     "storage" = "Drupal\event_venue\VenueStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\event_venue\VenueListBuilder",
 *     "views_data" = "Drupal\event_venue\Entity\VenueViewsData",
 *     "translation" = "Drupal\event_venue\VenueTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\event_venue\Form\VenueForm",
 *       "add" = "Drupal\event_venue\Form\VenueForm",
 *       "edit" = "Drupal\event_venue\Form\VenueForm",
 *       "delete" = "Drupal\event_venue\Form\VenueDeleteForm",
 *     },
 *     "access" = "Drupal\event_venue\VenueAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\event_venue\VenueHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "venue",
 *   data_table = "venue_field_data",
 *   revision_table = "venue_revision",
 *   revision_data_table = "venue_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer venue entities",
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
 *     "canonical" = "/admin/content/venue/{venue}",
 *     "add-page" = "/admin/content/venue/add",
 *     "add-form" = "/admin/content/venue/add/{venue_type}",
 *     "edit-form" = "/admin/content/venue/{venue}/edit",
 *     "delete-form" = "/admin/content/venue/{venue}/delete",
 *     "version-history" = "/admin/content/venue/{venue}/revisions",
 *     "revision" = "/admin/content/venue/{venue}/revisions/{venue_revision}/view",
 *     "revision_revert" = "/admin/content/venue/{venue}/revisions/{venue_revision}/revert",
 *     "revision_delete" = "/admin/content/venue/{venue}/revisions/{venue_revision}/delete",
 *     "translation_revert" = "/admin/content/venue/{venue}/revisions/{venue_revision}/revert/{langcode}",
 *     "collection" = "/admin/content/venue",
 *   },
 *   bundle_entity_type = "venue_type",
 *   field_ui_base_route = "entity.venue_type.edit_form"
 * )
 */
class Venue extends RevisionableContentEntityBase implements VenueInterface {

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

    // If no revision author has been set explicitly, make the venue owner the
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
      ->setDescription(t('The name of the Venue entity.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -40,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -40,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
  
    $fields['address'] = BaseFieldDefinition::create('generic_address')
      ->setLabel(t('Address'))
      ->setTranslatable(FALSE)
      ->setCardinality(1)
      ->setDisplayOptions('view', array(
        'type' => 'generic_address',
        'weight' => -3,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'generic_address',
        'weight' => -3,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
  
    $fields['geolocation'] = BaseFieldDefinition::create('geolocation')
      ->setLabel(t('Geolocation'))
      ->setTranslatable(FALSE)
      ->setCardinality(1)
      ->setDisplayOptions('view', array(
        'type' => 'geolocation_googlemap',
        'weight' => -3,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'geolocation_latlng',
        'weight' => -3,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
  
    $fields['capacity'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Capacity'))
      ->setDescription(t('Venue capacity'))
      ->setRevisionable(TRUE)
      ->setSettings(array(
        'min' => 0,
        'text_processing' => 0,
      ))
      ->setDisplayOptions('view', array(
        'type' => 'string',
        'weight' => -2,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'number',
        'weight' => -2,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Venue is published.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', FALSE);
  
    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Venue entity.'))
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
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE);
    
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
