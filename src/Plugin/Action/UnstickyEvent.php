<?php

namespace Drupal\event\Plugin\Action;

use Drupal\Core\Field\FieldUpdateActionBase;
use Drupal\event\EventInterface;

/**
 * Makes a event not sticky.
 *
 * @Action(
 *   id = "event_make_unsticky_action",
 *   label = @Translation("Make selected event not sticky"),
 *   type = "event"
 * )
 */
class UnstickyEvent extends FieldUpdateActionBase {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsToUpdate() {
    return ['sticky' => EventInterface::NOT_STICKY];
  }

}
