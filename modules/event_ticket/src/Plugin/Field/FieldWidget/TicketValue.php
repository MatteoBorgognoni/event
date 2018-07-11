<?php

namespace Drupal\event_ticket\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldFilteredMarkup;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\NumberWidget;

/**
 * Plugin implementation of the 'number' widget form the ticket_value field.
 *
 * @FieldWidget(
 *   id = "ticket_value_default",
 *   label = @Translation("Ticket value"),
 *   field_types = {
 *     "ticket_value"
 *   }
 * )
 */
class TicketValue extends NumberWidget {



  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $value = isset($items[$delta]->value) ? $items[$delta]->value : NULL;
    $field_settings = $this->getFieldSettings();

    $element += [
      '#type' => 'number',
      '#default_value' => $value / 100,
      '#placeholder' => $this->getSetting('placeholder'),
      '#min' => 0,
      '#step' => 0.01,
    ];
    
    if($this->fieldDefinition->getType() !== 'ticket_value') {
      // Set minimum and maximum.
      if (is_numeric($field_settings['min'])) {
        $element['#min'] = $field_settings['min'];
      }
      if (is_numeric($field_settings['max'])) {
        $element['#max'] = $field_settings['max'];
      }
  
      // Add prefix and suffix.
      if ($field_settings['prefix']) {
        $prefixes = explode('|', $field_settings['prefix']);
        $element['#field_prefix'] = FieldFilteredMarkup::create(array_pop($prefixes));
      }
      if ($field_settings['suffix']) {
        $suffixes = explode('|', $field_settings['suffix']);
        $element['#field_suffix'] = FieldFilteredMarkup::create(array_pop($suffixes));
      }
    }


    return ['value' => $element];
  }

  /**
   * {@inheritdoc}
   */
  public function errorElement(array $element, ConstraintViolationInterface $violation, array $form, FormStateInterface $form_state) {
    return $element['value'];
  }
  
  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    
    foreach ($values as $key => $value) {
      $amount = $value['value'] * 100;
      $values[$key]['value'] = $amount;
    }
    
    return $values;
  }
  
}
