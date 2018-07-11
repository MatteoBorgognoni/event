<?php

namespace Drupal\event\Plugin\views\row;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\views\Plugin\views\row\RssPluginBase;

/**
 * Plugin which performs a event_view on the resulting object
 * and formats it as an RSS item.
 *
 * @ViewsRow(
 *   id = "event_rss",
 *   title = @Translation("Events"),
 *   help = @Translation("Display the event with standard event view."),
 *   theme = "views_view_row_rss",
 *   register_theme = FALSE,
 *   base = {"event_field_data"},
 *   display_types = {"feed"}
 * )
 */
class Rss extends RssPluginBase {

  // Basic properties that let the row style follow relationships.
  public $base_table = 'event_field_data';

  public $base_field = 'eid';

  // Stores the events loaded with preRender.
  public $events = [];

  /**
   * {@inheritdoc}
   */
  protected $entityTypeId = 'event';

  /**
   * The event storage
   *
   * @var \Drupal\event\EventStorageInterface
   */
  protected $eventStorage;

  /**
   * Constructs the Rss object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityManagerInterface $entity_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_manager);
    $this->eventStorage = $entity_manager->getStorage('event');
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm_summary_options() {
    $options = parent::buildOptionsForm_summary_options();
    $options['title'] = $this->t('Title only');
    $options['default'] = $this->t('Use site default RSS settings');
    return $options;
  }

  public function summaryTitle() {
    $options = $this->buildOptionsForm_summary_options();
    return $options[$this->options['view_mode']];
  }

  public function preRender($values) {
    $eids = [];
    foreach ($values as $row) {
      $eids[] = $row->{$this->field_alias};
    }
    if (!empty($eids)) {
      $this->events = $this->eventStorage->loadMultiple($eids);
    }
  }

  public function render($row) {
    global $base_url;

    $eid = $row->{$this->field_alias};
    if (!is_numeric($eid)) {
      return;
    }

    $display_mode = $this->options['view_mode'];
    if ($display_mode == 'default') {
      $display_mode = \Drupal::config('system.rss')->get('items.view_mode');
    }

    // Load the specified event:
    /** @var \Drupal\event\EventInterface $event */
    $event = $this->events[$eid];
    if (empty($event)) {
      return;
    }

    $event->link = $event->url('canonical', ['absolute' => TRUE]);
    $event->rss_namespaces = [];
    $event->rss_elements = [
      [
        'key' => 'pubDate',
        'value' => gmdate('r', $event->getCreatedTime()),
      ],
      [
        'key' => 'dc:creator',
        'value' => $event->getOwner()->getDisplayName(),
      ],
      [
        'key' => 'guid',
        'value' => $event->id() . ' at ' . $base_url,
        'attributes' => ['isPermaLink' => 'false'],
      ],
    ];

    // The event gets built and modules add to or modify $event->rss_elements
    // and $event->rss_namespaces.

    $build_mode = $display_mode;

    $build = event_view($event, $build_mode);
    unset($build['#theme']);

    if (!empty($event->rss_namespaces)) {
      $this->view->style_plugin->namespaces = array_merge($this->view->style_plugin->namespaces, $event->rss_namespaces);
    }
    elseif (function_exists('rdf_get_namespaces')) {
      // Merge RDF namespaces in the XML namespaces in case they are used
      // further in the RSS content.
      $xml_rdf_namespaces = [];
      foreach (rdf_get_namespaces() as $prefix => $uri) {
        $xml_rdf_namespaces['xmlns:' . $prefix] = $uri;
      }
      $this->view->style_plugin->namespaces += $xml_rdf_namespaces;
    }

    $item = new \stdClass();
    if ($display_mode != 'title') {
      // We render events.
      $item->description = $build;
    }
    $item->title = $event->label();
    $item->link = $event->link;
    // Provide a reference so that the render call in
    // template_preprocess_views_view_row_rss() can still access it.
    $item->elements = &$event->rss_elements;
    $item->eid = $event->id();
    $build = [
      '#theme' => $this->themeFunctions(),
      '#view' => $this->view,
      '#options' => $this->options,
      '#row' => $item,
    ];

    return $build;
  }

}
