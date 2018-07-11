<?php

namespace Drupal\event_ticket\Plugin\Field\FieldType;

use Drupal\Core\Field\Plugin\Field\FieldType\StringItem;

/**
 * Plugin implementation of the 'ticket_value' field type.
 *
 * @FieldType(
 *   id = "ticket_value",
 *   label = @Translation("Ticket value"),
 *   description = @Translation("Amount field type for the Event ticket module"),
 *   default_widget = "ticket_value_default",
 *   default_formatter = "ticket_value_default",
 * )
 */
class TicketValue extends StringItem {


}
