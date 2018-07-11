<?php

/**
 * @file
 * Post update functions for Event.
 */

use Drupal\Core\Entity\Entity\EntityFormDisplay;

/**
* Load all form displays for events, add status with these settings, save.
*/
function event_post_update_configure_status_field_widget() {
  $query = \Drupal::entityQuery('entity_form_display')->condition('targetEntityType', 'event');
  $ids = $query->execute();
  $form_displays = EntityFormDisplay::loadMultiple($ids);

  // Assign status settings for each 'event' target entity types with 'default'
  // form mode.
  foreach ($form_displays as $id => $form_display) {
    /** @var \Drupal\Core\Entity\Display\EntityDisplayInterface $form_display */
    $form_display->setComponent('status', [
      'type' => 'boolean_checkbox',
      'settings' => [
        'display_label' => TRUE,
      ],
    ])->save();
  }
}
