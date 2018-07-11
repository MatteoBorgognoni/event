<?php

namespace Drupal\event\Plugin\Action;

use Drupal\Core\Field\FieldUpdateActionBase;
use Drupal\event\EventInterface;

/**
 * Makes a event sticky.
 *
 * @Action(
 *   id = "event_make_sticky_action",
 *   label = @Translation("Make selected event sticky"),
 *   type = "event"
 * )
 */
class StickyEvent extends FieldUpdateActionBase {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsToUpdate() {
    return ['sticky' => EventInterface::STICKY];
  }

}
