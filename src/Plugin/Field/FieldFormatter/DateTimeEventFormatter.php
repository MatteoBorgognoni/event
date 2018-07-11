<?php

namespace Drupal\event\Plugin\Field\FieldFormatter;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime\Plugin\Field\FieldFormatter\DateTimeDefaultFormatter;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'Default' formatter for 'datetime' fields.
 *
 * @FieldFormatter(
 *   id = "datetime_event",
 *   label = @Translation("Event date"),
 *   field_types = {
 *     "datetime"
 *   }
 * )
 */
class DateTimeEventFormatter extends DateTimeDefaultFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    $field_name = $this->fieldDefinition->getName();

    switch ($field_name) {
      case 'date_start':
        $method = 'isStartDateHidden';
        break;
      case 'date_end':
        $method = 'isEndDateHidden';
        break;
    }
    /** @var \Drupal\Core\Entity\EntityInterface $event */
    $event = $items->getEntity();
    $date_is_hidden = $event->{$method}();

    foreach ($items as $delta => $item) {
      if ($item->date && !$date_is_hidden) {
        /** @var \Drupal\Core\Datetime\DrupalDateTime $date */
        $date = $item->date;
        $elements[$delta] = $this->buildDateWithIsoAttribute($date);

        if (!empty($item->_attributes)) {
          $elements[$delta]['#attributes'] += $item->_attributes;
          // Unset field item attributes since they have been included in the
          // formatter output and should not be rendered in the field template.
          unset($item->_attributes);
        }
      }
      else {
        $elements[$delta] = [];
      }
    }

    return $elements;
  }

}
