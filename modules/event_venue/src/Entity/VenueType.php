<?php

namespace Drupal\event_venue\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Venue type entity.
 *
 * @ConfigEntityType(
 *   id = "venue_type",
 *   label = @Translation("Venue type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\event_venue\VenueTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\event_venue\Form\VenueTypeForm",
 *       "edit" = "Drupal\event_venue\Form\VenueTypeForm",
 *       "delete" = "Drupal\event_venue\Form\VenueTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\event_venue\VenueTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "venue_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "venue",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/venue_type/{venue_type}",
 *     "add-form" = "/admin/structure/venue_type/add",
 *     "edit-form" = "/admin/structure/venue_type/{venue_type}/edit",
 *     "delete-form" = "/admin/structure/venue_type/{venue_type}/delete",
 *     "collection" = "/admin/structure/venue_type"
 *   }
 * )
 */
class VenueType extends ConfigEntityBundleBase implements VenueTypeInterface {

  /**
   * The Venue type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Venue type label.
   *
   * @var string
   */
  protected $label;

}
