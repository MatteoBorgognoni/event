<?php

namespace Drupal\event\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\UnpublishAction;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Unpublishes a event.
 *
 * @deprecated in Drupal 8.5.x, to be removed before Drupal 9.0.0.
 *   Use \Drupal\Core\Action\Plugin\Action\UnpublishAction instead.
 *
 * @see \Drupal\Core\Action\Plugin\Action\UnpublishAction
 * @see https://www.drupal.org/event/2919303
 *
 * @Action(
 *   id = "event_unpublish_action",
 *   label = @Translation("Unpublish selected event"),
 *   type = "event"
 * )
 */
class UnpublishEvent extends UnpublishAction {

  /**
   * {@inheritdoc}
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager);
    @trigger_error(__NAMESPACE__ . '\UnpublishEvent is deprecated in Drupal 8.5.x, will be removed before Drupal 9.0.0. Use \Drupal\Core\Action\Plugin\Action\UnpublishAction instead. See https://www.drupal.org/event/2919303.', E_USER_DEPRECATED);
  }

}
