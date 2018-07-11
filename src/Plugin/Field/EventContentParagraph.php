<?php

namespace Drupal\event\Plugin\Field;

use Drupal\Core\TypedData\ComputedItemListTrait;
use Drupal\entity_reference_revisions\EntityReferenceRevisionsFieldItemList;

class EventContentParagraph extends EntityReferenceRevisionsFieldItemList  {
  
  use ComputedItemListTrait;
  
  /**
   * Compute the values.
   */
  protected function computeValue() {
    /** @var \Drupal\event\EventInterface $event */
    $event = $this->getEntity();

    $field_name = $this->getFieldDefinition()->getSettings()['field_name'];
    $event_content = $event->getContent();

    if($event_content) {
      /** @var \Drupal\Core\Field\FieldItemList $values */
      $values = $event_content->get($field_name)->getValue();
      foreach ($values as $delta => $value) {
        $this->list[$delta] = $this->createItem($delta, $value);
      }
    }
  }
  
}