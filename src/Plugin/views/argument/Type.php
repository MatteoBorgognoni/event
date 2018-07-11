<?php

namespace Drupal\event\Plugin\views\argument;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\views\Plugin\views\argument\StringArgument;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Argument handler to accept a event type.
 *
 * @ViewsArgument("event_type")
 */
class Type extends StringArgument {

  /**
   * EventType storage handler.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $eventTypeStorage;

  /**
   * Constructs a new Event Type object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $event_type_storage
   *   The entity storage class.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $event_type_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->eventTypeStorage = $event_type_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $entity_manager = $container->get('entity.manager');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $entity_manager->getStorage('event_type')
    );
  }

  /**
   * Override the behavior of summaryName(). Get the user friendly version
   * of the event type.
   */
  public function summaryName($data) {
    return $this->event_type($data->{$this->name_alias});
  }

  /**
   * Override the behavior of title(). Get the user friendly version of the
   * event type.
   */
  public function title() {
    return $this->event_type($this->argument);
  }

  public function event_type($type_name) {
    $type = $this->eventTypeStorage->load($type_name);
    $output = $type ? $type->label() : $this->t('Unknown content type');
    return $output;
  }

}
