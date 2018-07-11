<?php

namespace Drupal\event_ticket\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Plugin\Field\FieldFormatter\StringFormatter;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'ticket_value_default' formatter.
 *
 * @FieldFormatter(
 *   id = "ticket_value_default",
 *   label = @Translation("Ticket value"),
 *   field_types = {
 *     "ticket_value"
 *   }
 * )
 */
class TicketValue extends StringFormatter {

  
  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    /** @var \Drupal\event_ticket\Entity\TicketInterface $ticket */
    $ticket = $items->getEntity();

    foreach ($items as $delta => $item) {
      $view_value = (int) $item->value;
      $elements[$delta] = [
        '#markup' => $ticket->getFormattedValue(),
      ];
      

    }
    return $elements;
  }
  

}
