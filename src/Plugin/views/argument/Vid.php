<?php

namespace Drupal\event\Plugin\views\argument;

use Drupal\Core\Database\Connection;
use Drupal\views\Plugin\views\argument\NumericArgument;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\event\EventStorageInterface;

/**
 * Argument handler to accept a event revision id.
 *
 * @ViewsArgument("event_vid")
 */
class Vid extends NumericArgument {

  /**
   * Database Service Object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The event storage.
   *
   * @var \Drupal\event\EventStorageInterface
   */
  protected $eventStorage;

  /**
   * Constructs a Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Database\Connection $database
   *   Database Service Object.
   * @param \Drupal\event\EventStorageInterface $event_storage
   *   The event storage.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $database, EventStorageInterface $event_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->database = $database;
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
      $container->get('database'),
      $container->get('entity.manager')->getStorage('event')
    );
  }

  /**
   * Override the behavior of title(). Get the title of the revision.
   */
  public function titleQuery() {
    $titles = [];

    $results = $this->database->query('SELECT nr.vid, nr.eid, npr.title FROM {event_revision} nr WHERE nr.vid IN ( :vids[] )', [':vids[]' => $this->value])->fetchAllAssoc('vid', PDO::FETCH_ASSOC);
    $eids = [];
    foreach ($results as $result) {
      $eids[] = $result['eid'];
    }

    $events = $this->eventStorage->loadMultiple(array_unique($eids));

    foreach ($results as $result) {
      $events[$result['eid']]->set('title', $result['title']);
      $titles[] = $events[$result['eid']]->label();
    }

    return $titles;
  }

}
