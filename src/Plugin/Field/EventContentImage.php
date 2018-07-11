<?php

namespace Drupal\event\Plugin\Field;

use Drupal\Core\Field\EntityReferenceFieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;

class EventContentImage extends EntityReferenceFieldItemList  {
  
  use ComputedItemListTrait;
  
  /**
   * Compute the values.
   */
  protected function computeValue() {
    /** @var \Drupal\event\EventInterface $event */
    $event = $this->getEntity();
    $event_content = $event->getContent();

    $field_name = $this->getFieldDefinition()->getSettings()['field_name'];

    if($event_content) {
      /** @var \Drupal\Core\Field\FieldItemList $values */
      $values = $event_content->get($field_name)->getValue();
      foreach ($values as $delta => $value) {
        $this->list[$delta] = $this->createItem($delta, $value);
      }
    }
  }
  
}