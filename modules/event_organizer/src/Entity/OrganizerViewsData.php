<?php

namespace Drupal\event_organizer\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Organizer entities.
 */
class OrganizerViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.

    return $data;
  }

}
