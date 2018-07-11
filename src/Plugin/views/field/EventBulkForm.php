<?php

namespace Drupal\event\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm;

/**
 * Defines a event operations bulk form element.
 *
 * @ViewsField("event_bulk_form")
 */
class EventBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No event selected.');
  }

}
