<?php

namespace Drupal\event\Plugin\Action;

use Drupal\Core\Field\FieldUpdateActionBase;
use Drupal\event\EventInterface;

/**
 * Promotes a event.
 *
 * @Action(
 *   id = "event_promote_action",
 *   label = @Translation("Promote selected event to front page"),
 *   type = "event"
 * )
 */
class PromoteEvent extends FieldUpdateActionBase {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsToUpdate() {
    return ['promote' => EventInterface::PROMOTED];
  }

}
