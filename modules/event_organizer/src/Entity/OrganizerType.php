<?php

namespace Drupal\event_organizer\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Organizer type entity.
 *
 * @ConfigEntityType(
 *   id = "organizer_type",
 *   label = @Translation("Organizer type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\event_organizer\OrganizerTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\event_organizer\Form\OrganizerTypeForm",
 *       "edit" = "Drupal\event_organizer\Form\OrganizerTypeForm",
 *       "delete" = "Drupal\event_organizer\Form\OrganizerTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\event_organizer\OrganizerTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "organizer_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "organizer",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/organizer_type/{organizer_type}",
 *     "add-form" = "/admin/structure/organizer_type/add",
 *     "edit-form" = "/admin/structure/organizer_type/{organizer_type}/edit",
 *     "delete-form" = "/admin/structure/organizer_type/{organizer_type}/delete",
 *     "collection" = "/admin/structure/organizer_type"
 *   }
 * )
 */
class OrganizerType extends ConfigEntityBundleBase implements OrganizerTypeInterface {

  /**
   * The Organizer type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Organizer type label.
   *
   * @var string
   */
  protected $label;

}
