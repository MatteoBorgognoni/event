<?php

namespace Drupal\event;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilder;

/**
 * View builder handler for events.
 */
class EventViewBuilder extends EntityViewBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildComponents(array &$build, array $entities, array $displays, $view_mode) {
    /** @var \Drupal\event\EventInterface[] $entities */
    if (empty($entities)) {
      return;
    }

    $entities_by_bundle = [];
    foreach ($entities as $id => $entity) {
      // Initialize the field item attributes for the fields being displayed.
      // The entity can include fields that are not displayed, and the display
      // can include components that are not fields, so we want to act on the
      // intersection. However, the entity can have many more fields than are
      // displayed, so we avoid the cost of calling $entity->getProperties()
      // by iterating the intersection as follows.
      foreach ($displays[$entity->bundle()]->getComponents() as $name => $options) {
        if ($entity->hasField($name)) {
          foreach ($entity->get($name) as $item) {
            $item->_attributes = [];
          }
        }
      }
      // Group the entities by bundle.
      $entities_by_bundle[$entity->bundle()][$id] = $entity;
    }

    // Invoke hook_entity_prepare_view().
    $this->moduleHandler()->invokeAll('entity_prepare_view', [$this->entityTypeId, $entities, $displays, $view_mode]);

    // Let the displays build their render arrays.
    foreach ($entities_by_bundle as $bundle => $bundle_entities) {
      $display_build = $displays[$bundle]->buildMultiple($bundle_entities);
      foreach ($bundle_entities as $id => $entity) {
        $build[$id] += $display_build[$id];
      }
    }

    foreach ($entities as $id => $entity) {
      $bundle = $entity->bundle();
      $display = $displays[$bundle];

      if ($display->getComponent('links')) {
        $build[$id]['links'] = [
          '#lazy_builder' => [
            get_called_class() . '::renderLinks', [
              $entity->id(),
              $view_mode,
              $entity->language()->getId(),
              !empty($entity->in_preview),
              $entity->isDefaultRevision() ? NULL : $entity->getLoadedRevisionId(),
            ],
          ],
        ];
      }

      // Add Language field text element to event render array.
      if ($display->getComponent('langcode')) {
        $build[$id]['langcode'] = [
          '#type' => 'item',
          '#title' => t('Language'),
          '#markup' => $entity->language()->getName(),
          '#prefix' => '<div id="field-language-display">',
          '#suffix' => '</div>'
        ];
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getBuildDefaults(EntityInterface $entity, $view_mode) {
    $defaults = parent::getBuildDefaults($entity, $view_mode);

    // Don't cache events that are in 'preview' mode.
    if (isset($defaults['#cache']) && isset($entity->in_preview)) {
      unset($defaults['#cache']);
    }

    return $defaults;
  }

  /**
   * #lazy_builder callback; builds a event's links.
   *
   * @param string $event_entity_id
   *   The event entity ID.
   * @param string $view_mode
   *   The view mode in which the event entity is being viewed.
   * @param string $langcode
   *   The language in which the event entity is being viewed.
   * @param bool $is_in_preview
   *   Whether the event is currently being previewed.
   * @param $revision_id
   *   (optional) The identifier of the event revision to be loaded. If none
   *   is provided, the default revision will be loaded.
   *
   * @return array
   *   A renderable array representing the event links.
   */
  public static function renderLinks($event_entity_id, $view_mode, $langcode, $is_in_preview, $revision_id = NULL) {
    $links = [
      '#theme' => 'links__event',
      '#pre_render' => ['drupal_pre_render_links'],
      '#attributes' => ['class' => ['links', 'inline']],
    ];

    if (!$is_in_preview) {
      $storage = \Drupal::entityTypeManager()->getStorage('event');
      /** @var \Drupal\event\EventInterface $revision */
      $revision = !isset($revision_id) ? $storage->load($event_entity_id) : $storage->loadRevision($revision_id);
      $entity = $revision->getTranslation($langcode);
      $links['event'] = static::buildLinks($entity, $view_mode);

      // Allow other modules to alter the event links.
      $hook_context = [
        'view_mode' => $view_mode,
        'langcode' => $langcode,
      ];
      \Drupal::moduleHandler()->alter('event_links', $links, $entity, $hook_context);
    }
    return $links;
  }

  /**
   * Build the default links (Read more) for a event.
   *
   * @param \Drupal\event\EventInterface $entity
   *   The event object.
   * @param string $view_mode
   *   A view mode identifier.
   *
   * @return array
   *   An array that can be processed by drupal_pre_render_links().
   */
  protected static function buildLinks(EventInterface $entity, $view_mode) {
    $links = [];

    // Always display a read more link on teasers because we have no way
    // to know when a teaser view is different than a full view.
    if ($view_mode == 'teaser') {
      $event_title_stripped = strip_tags($entity->label());
      $links['event-readmore'] = [
        'title' => t('Read more<span class="visually-hidden"> about @title</span>', [
          '@title' => $event_title_stripped,
        ]),
        'url' => $entity->urlInfo(),
        'language' => $entity->language(),
        'attributes' => [
          'rel' => 'tag',
          'title' => $event_title_stripped,
        ],
      ];
    }

    return [
      '#theme' => 'links__event__event',
      '#links' => $links,
      '#attributes' => ['class' => ['links', 'inline']],
    ];
  }

}
