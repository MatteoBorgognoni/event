<?php

namespace Drupal\event\Plugin\Field;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;

class GeolocationFieldReference extends FieldItemList  {
  
  use ComputedItemListTrait;
  
  /**
   * Compute the values.
   */
  protected function computeValue() {
    /** @var \Drupal\event\EventInterface $event */
    $event = $this->getEntity();
    $entity_type = $this->getFieldDefinition()->getSettings()['entity_type'];
    $field_name = $this->getFieldDefinition()->getSettings()['field_name'];
    /** @var \Drupal\Core\Entity\EntityInterface $referenced_entity */
    $referenced_entity = $event->get($entity_type)->entity;
    $list = [];
    if($referenced_entity) {
      /** @var \Drupal\Core\Field\FieldItemList $values */
      $values = $referenced_entity->get($field_name)->getValue();
      foreach ($values as $delta => $value) {
        if(isset($value['data']) && !is_array($value['data'])) {
          $value['data'] = unserialize($value['data']);
        }
        $list[$delta] = $this->createItem($delta, $value);;
      }
    }
    $this->list = $list;
  }
  
}