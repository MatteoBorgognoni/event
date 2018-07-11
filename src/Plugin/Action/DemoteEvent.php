<?php

namespace Drupal\event\Plugin\Action;

use Drupal\Core\Field\FieldUpdateActionBase;
use Drupal\event\EventInterface;

/**
 * Demotes a event.
 *
 * @Action(
 *   id = "event_unpromote_action",
 *   label = @Translation("Demote selected event from front page"),
 *   type = "event"
 * )
 */
class DemoteEvent extends FieldUpdateActionBase {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsToUpdate() {
    return ['promote' => EventInterface::NOT_PROMOTED];
  }

}
