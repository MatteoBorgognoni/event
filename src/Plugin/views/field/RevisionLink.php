<?php

namespace Drupal\event\Plugin\views\field;

use Drupal\Core\Url;
use Drupal\views\Plugin\views\field\LinkBase;
use Drupal\views\ResultRow;

/**
 * Field handler to present a link to a event revision.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("event_revision_link")
 */
class RevisionLink extends LinkBase {

  /**
   * {@inheritdoc}
   */
  protected function getUrlInfo(ResultRow $row) {
    /** @var \Drupal\event\EventInterface $event */
    $event = $this->getEntity($row);
    // Current revision uses the event view path.
    return !$event->isDefaultRevision() ?
      Url::fromRoute('entity.event.revision', ['event' => $event->id(), 'event_revision' => $event->getRevisionId()]) :
      $event->urlInfo();
  }

  /**
   * {@inheritdoc}
   */
  protected function renderLink(ResultRow $row) {
    /** @var \Drupal\event\EventInterface $event */
    $event = $this->getEntity($row);
    if (!$event->getRevisionId()) {
      return '';
    }
    $text = parent::renderLink($row);
    $this->options['alter']['query'] = $this->getDestinationArray();
    return $text;
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultLabel() {
    return $this->t('View');
  }

}
