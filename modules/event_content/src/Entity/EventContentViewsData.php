<?php

namespace Drupal\event_content\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Event content entities.
 */
class EventContentViewsData extends EntityViewsData {

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
