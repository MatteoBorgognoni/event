<?php

namespace Drupal\event\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Controller\EntityViewController;

/**
 * Defines a controller to render a single event in preview.
 */
class EventPreviewController extends EntityViewController {

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $event_preview, $view_mode_id = 'full', $langcode = NULL) {
    $event_preview->preview_view_mode = $view_mode_id;
    $build = parent::view($event_preview, $view_mode_id);

    $build['#attached']['library'][] = 'event/drupal.event.preview';

    // Don't render cache previews.
    unset($build['#cache']);

    return $build;
  }

  /**
   * The _title_callback for the page that renders a single event in preview.
   *
   * @param \Drupal\Core\Entity\EntityInterface $event_preview
   *   The current event.
   *
   * @return string
   *   The page title.
   */
  public function title(EntityInterface $event_preview) {
    return $this->entityManager->getTranslationFromContext($event_preview)->label();
  }

}
