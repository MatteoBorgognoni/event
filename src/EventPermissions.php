<?php

namespace Drupal\event;

use Drupal\Core\Routing\UrlGeneratorTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\event\Entity\EventType;

/**
 * Provides dynamic permissions for events of different types.
 */
class EventPermissions {

  use StringTranslationTrait;
  use UrlGeneratorTrait;

  /**
   * Returns an array of event type permissions.
   *
   * @return array
   *   The event type permissions.
   *   @see \Drupal\user\PermissionHandlerInterface::getPermissions()
   */
  public function eventTypePermissions() {
    $perms = [];
    // Generate event permissions for all event types.
    foreach (EventType::loadMultiple() as $type) {
      $perms += $this->buildPermissions($type);
    }

    return $perms;
  }

  /**
   * Returns a list of event permissions for a given event type.
   *
   * @param \Drupal\event\Entity\EventType $type
   *   The event type.
   *
   * @return array
   *   An associative array of permission names and descriptions.
   */
  protected function buildPermissions(EventType $type) {
    $type_id = $type->id();
    $type_params = ['%type_name' => $type->label()];

    return [
      "create $type_id event" => [
        'title' => $this->t('%type_name: Create new event', $type_params),
      ],
      "edit own $type_id event" => [
        'title' => $this->t('%type_name: Edit own event', $type_params),
      ],
      "edit any $type_id event" => [
        'title' => $this->t('%type_name: Edit any event', $type_params),
      ],
      "delete own $type_id event" => [
        'title' => $this->t('%type_name: Delete own event', $type_params),
      ],
      "delete any $type_id event" => [
        'title' => $this->t('%type_name: Delete any event', $type_params),
      ],
      "view $type_id revisions" => [
        'title' => $this->t('%type_name: View revisions', $type_params),
        'description' => t('To view a revision, you also need permission to view the event item.'),
      ],
      "revert $type_id revisions" => [
        'title' => $this->t('%type_name: Revert revisions', $type_params),
        'description' => t('To revert a revision, you also need permission to edit the event item.'),
      ],
      "delete $type_id revisions" => [
        'title' => $this->t('%type_name: Delete revisions', $type_params),
        'description' => $this->t('To delete a revision, you also need permission to delete the event item.'),
      ],
    ];
  }

}
