<?php

namespace Drupal\event_ticket\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Ticket type entity.
 *
 * @ConfigEntityType(
 *   id = "ticket_type",
 *   label = @Translation("Ticket type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\event_ticket\TicketTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\event_ticket\Form\TicketTypeForm",
 *       "edit" = "Drupal\event_ticket\Form\TicketTypeForm",
 *       "delete" = "Drupal\event_ticket\Form\TicketTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\event_ticket\TicketTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "ticket_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "ticket",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/ticket_type/{ticket_type}",
 *     "add-form" = "/admin/structure/ticket_type/add",
 *     "edit-form" = "/admin/structure/ticket_type/{ticket_type}/edit",
 *     "delete-form" = "/admin/structure/ticket_type/{ticket_type}/delete",
 *     "collection" = "/admin/structure/ticket_type"
 *   }
 * )
 */
class TicketType extends ConfigEntityBundleBase implements TicketTypeInterface {

  /**
   * The Ticket type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Ticket type label.
   *
   * @var string
   */
  protected $label;

}
