<?php

namespace Drupal\event_ticket\Plugin\Field;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;
use Drupal\event_ticket\Entity\Ticket;

class TicketFormattedValue extends FieldItemList  {

  use ComputedItemListTrait;

  /**
   * Compute the values.
   */
  protected function computeValue() {
    /** @var \Drupal\event_ticket\Entity\Ticket $event */
    $ticket = $this->getEntity();
    $is_free = $ticket->get('is_free')->value;
    $field_name = $this->getFieldDefinition()->getSettings()['field_name'];
    $values = $ticket->get($field_name)->getValue();

    $final_value = $is_free ? 'Free' : $ticket->getFormattedValue();

    foreach ($values as $delta => $value) {
      $this->list[$delta] = $this->createItem($delta, $final_value);
    }
  }

}