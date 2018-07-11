<?php

namespace Drupal\event\Plugin\Action;

use Drupal\Core\Action\Plugin\Action\PublishAction;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Publishes a event.
 *
 * @deprecated in Drupal 8.5.x, to be removed before Drupal 9.0.0.
 *   Use \Drupal\Core\Action\Plugin\Action\PublishAction instead.
 *
 * @see \Drupal\Core\Action\Plugin\Action\PublishAction
 * @see https://www.drupal.org/event/2919303
 *
 * @Action(
 *   id = "event_publish_action",
 *   label = @Translation("Publish selected event"),
 *   type = "event"
 * )
 */
class PublishEvent extends PublishAction {

  /**
   * {@inheritdoc}
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager);
    @trigger_error(__NAMESPACE__ . '\PublishEvent is deprecated in Drupal 8.5.x, will be removed before Drupal 9.0.0. Use \Drupal\Core\Action\Plugin\Action\PublishAction instead. See https://www.drupal.org/event/2919303.', E_USER_DEPRECATED);
  }

}
