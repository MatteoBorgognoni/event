<?php

namespace Drupal\event_eventbrite\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Plugin\Field\FieldFormatter\StringFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\SortArray;
use Drupal\donation\DonationWrapper;
use Drupal\event\Plugin\Field\FieldFormatter\RegistrationButtonDefault;


/**
 * Plugin implementation of the 'registration_button_eventbrite' formatter.
 *
 * @FieldFormatter(
 *   id = "registration_button_eventbrite",
 *   label = @Translation("Eventbrite Registration Button"),
 *   field_types = {
 *     "registration_button"
 *   }
 * )
 */
class RegistrationButtonEventBrite extends RegistrationButtonDefault {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    /** @var \Drupal\event\EventInterface $event */
    $event = $items->getEntity();

    $eventbrite_id = $event->get('eventbrite_id')->value;
    $eventbrite_url = $event->get('eventbrite_url')->value;

    foreach ($items as $delta => $item) {
      $button_text = $this->viewValue($item);
      $elements[$delta] = [
        '#theme' => 'eventbrite_button',
        '#title' => $button_text,
        '#url' => $eventbrite_url,
        '#id' => $eventbrite_id
      ];
    }
    return $elements;
  }


}
