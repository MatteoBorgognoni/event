<?php

namespace Drupal\event\Plugin\views\argument;

use Drupal\event\EventStorageInterface;
use Drupal\views\Plugin\views\argument\NumericArgument;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Argument handler to accept a event id.
 *
 * @ViewsArgument("event_eid")
 */
class Eid extends NumericArgument {

  /**
   * The event storage.
   *
   * @var \Drupal\event\EventStorageInterface
   */
  protected $eventStorage;

  /**
   * Constructs the Eid object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\event\EventStorageInterface $event_storage
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EventStorageInterface $event_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->eventStorage = $event_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager')->getStorage('event')
    );
  }

  /**
   * Override the behavior of title(). Get the title of the event.
   */
  public function titleQuery() {
    $titles = [];

    $events = $this->eventStorage->loadMultiple($this->value);
    foreach ($events as $event) {
      $titles[] = $event->label();
    }
    return $titles;
  }

}
