<?php

namespace Drupal\event_eventbrite;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionListenerInterface;
use Drupal\Core\Field\FieldStorageDefinitionListenerInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Render\Element\Date;
use Drupal\event\EventManager;
use Symfony\Component\HttpFoundation\ParameterBag;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Class EventbriteParser.
 */
class EventbriteParser {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  /**
   * Drupal\Core\Field\FieldDefinitionListenerInterface definition.
   *
   * @var \Drupal\Core\Field\FieldDefinitionListenerInterface
   */
  protected $fieldDefinitionListener;
  /**
   * Drupal\Core\Field\FieldStorageDefinitionListenerInterface definition.
   *
   * @var \Drupal\Core\Field\FieldStorageDefinitionListenerInterface
   */
  protected $fieldStorageDefinitionListener;
  /**
   * Drupal\Core\Entity\EntityFieldManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;
  /**
   * Drupal\Core\Entity\EntityTypeBundleInfoInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;
  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;
  /**
   * Drupal\event_eventbrite\EventManager definition.
   *
   * @var \Drupal\event\EventManager
   */
  protected $eventManager;

  /** @var \Drupal\Core\Config\ImmutableConfig  */
  protected $fieldMap;

  /**
   * Constructs a new EventbriteParser object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, FieldDefinitionListenerInterface $field_definition_listener, FieldStorageDefinitionListenerInterface $field_storage_definition_listener, EntityFieldManagerInterface $entity_field_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info, ConfigFactoryInterface $config_factory, EventManager $event_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->fieldDefinitionListener = $field_definition_listener;
    $this->fieldStorageDefinitionListener = $field_storage_definition_listener;
    $this->entityFieldManager = $entity_field_manager;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    $this->configFactory = $config_factory;
    $this->eventManager = $event_manager;
    $this->fieldMap = $this->configFactory->get('event_eventbrite.fieldmap');
  }

  public function getEntityValues($entityType, ParameterBag $eventData, $bundle = NULL, $op) {
    $bag = new ParameterBag();
    $bundle_key = $this->entityTypeManager->getDefinition($entityType)->getKey('bundle');
    if($bundle_key) {
      $bag->set($bundle_key, $bundle);
    }
    $fieldMap = $this->fieldMap->get($entityType);

    foreach ($fieldMap as $field_name => $field_info) {
      $key = explode('.', $field_info['key']);
      if($eventData->has($key[0]) || $key[0] == 'ignore') {

        if($op == 'update' && isset($field_info['update']) && !$field_info['update']) {
          continue;
        }

        switch ($field_info['type']) {
          case 'string':
            $this->setStringValue($eventData, $bag, $field_name, $field_info);
            break;
          case 'integer':
            $this->setIntegerValue($eventData, $bag, $field_name, $field_info);
            break;
          case 'float':
            $this->setFloatValue($eventData, $bag, $field_name, $field_info);
            break;
          case 'datetime':
            $this->setDatetimeValue($eventData, $bag, $field_name, $field_info);
            break;
          case 'timestamp':
            $this->setTimestampValue($eventData, $bag, $field_name, $field_info);
            break;
          case 'status':
            $this->setStatusValue($eventData, $bag, $field_name, $field_info);
            break;
          case 'bool':
            $this->setBoolValue($eventData, $bag, $field_name, $field_info);
            break;
          case 'lang':
            $this->setLangValue($eventData, $bag, $field_name, $field_info);
            break;
          case 'reference':
            $this->setReferenceValue($eventData, $bag, $field_name, $field_info, $op);
            break;
          case 'multivalue':
            $this->setMultivalueValue($eventData, $bag, $field_name, $field_info);
            break;
          case 'media':
            $this->setMediaValue($eventData, $bag, $field_name, $field_info);
            break;
        }
      }
    }
    //ksm($bag->all(), $entityType, $bundle, $fieldMap, $eventData);
    return $bag;
  }

  public function getEntityUpdateValues($entityType, ParameterBag $eventData) {
    $event_values = new ParameterBag();
    return $event_values;
  }

  public function setStringValue(ParameterBag $eventData, ParameterBag $event_values, $field_name, $field_info) {
    $key = explode('.', $field_info['key']);
    $data = $eventData->all();
    $value = NestedArray::getValue($data, $key);
    $event_values->set($field_name, $value);
  }

  public function setIntegerValue(ParameterBag $eventData, ParameterBag $event_values, $field_name, $field_info) {
    $key = explode('.', $field_info['key']);
    $data = $eventData->all();
    $value = NestedArray::getValue($data, $key);
    $event_values->set($field_name, $value);
  }

  public function setAmountValue(ParameterBag $eventData, ParameterBag $event_values, $field_name, $field_info) {
    $key = explode('.', $field_info['key']);
    $data = $eventData->all();
    $value = NestedArray::getValue($data, $key);
    $event_values->set($field_name, $value / 100);
  }

  public function setFloatValue(ParameterBag $eventData, ParameterBag $event_values, $field_name, $field_info) {
    $key = explode('.', $field_info['key']);
    $data = $eventData->all();
    $value = NestedArray::getValue($data, $key);
    $event_values->set($field_name, $value);
  }

  public function setDatetimeValue(ParameterBag $eventData, ParameterBag $event_values, $field_name, $field_info) {
    $key = explode('.', $field_info['key']);
    $data = $eventData->all();
    $value = NestedArray::getValue($data, $key);
    $date = new \DateTime($value);
    $formatted = DrupalDateTime::createFromDateTime($date)->format(DATETIME_DATETIME_STORAGE_FORMAT);
    $event_values->set($field_name, $formatted);
  }

  public function setTimestampValue(ParameterBag $eventData, ParameterBag $event_values, $field_name, $field_info) {
    $key = explode('.', $field_info['key']);
    $data = $eventData->all();
    $value = strtotime(NestedArray::getValue($data, $key));
    $event_values->set($field_name, $value);
  }

  public function setStatusValue(ParameterBag $eventData, ParameterBag $event_values, $field_name, $field_info) {
    $key = explode('.', $field_info['key']);
    $data = $eventData->all();
    $value = NestedArray::getValue($data, $key);
    $event_values->set($field_name, $value);
    switch ($value) {
      case 'live':
        $event_values->set('moderation_state', 'published');
        $event_values->set('status', 1);
        break;
      default:
        $event_values->set('moderation_state', 'draft');
        $event_values->set('status', 0);
        break;
    }
  }

  public function setBoolValue(ParameterBag $eventData, ParameterBag $event_values, $field_name, $field_info) {
    $key = explode('.', $field_info['key']);
    $data = $eventData->all();
    $value = NestedArray::getValue($data, $key);
    $event_values->set($field_name, $value);
  }

  public function setLangValue(ParameterBag $eventData, ParameterBag $event_values, $field_name, $field_info) {}

  public function setReferenceValue(ParameterBag $eventData, ParameterBag $event_values, $field_name, $field_info, $op) {
    $key = explode('.', $field_info['key']);
    $data = $eventData->all();
    $entity_type = $field_info['entity_type'];
    $type = $field_info['bundle'];
    if(isset($field_info['multiple']) && $field_info['multiple']) {
      foreach ($data[$key[0]] as $i => $i_data) {
        $singleEventData = new ParameterBag($i_data);
        $entity_type_values = $this->getEntityValues($entity_type, $singleEventData, $type, $op);
      }
    }
    else {
      $entity_type_values = $this->getEntityValues($entity_type, $eventData, $type, $op);
    }

  }

  public function setMultivalueValue(ParameterBag $eventData, ParameterBag $event_values, $field_name, $field_info) {
    $multi_value = new ParameterBag();
    if(isset($field_info['children'])) {
      foreach ($field_info['children'] as $sub_field_name => $sub_field_info) {
        $method = 'set' . ucfirst($sub_field_info['type']) . 'Value';
        $this->{$method}($eventData, $multi_value, $sub_field_name, $sub_field_info);
      }
    }
    $event_values->set($field_name, $multi_value->all());
  }


  public function setMediaValue(ParameterBag $eventData, ParameterBag $event_values, $field_name, $field_info) {
    $key = explode('.', $field_info['key']);
    $data = $eventData->all();
    $value = NestedArray::getValue($data, $key);
    $event_values->set($field_name, $value);
  }

}
