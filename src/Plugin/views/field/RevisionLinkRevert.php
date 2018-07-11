<?php

namespace Drupal\event\Plugin\views\field;

use Drupal\Core\Url;
use Drupal\views\ResultRow;

/**
 * Field handler to present a link to revert a event to a revision.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("event_revision_link_revert")
 */
class RevisionLinkRevert extends RevisionLink {

  /**
   * {@inheritdoc}
   */
  protected function getUrlInfo(ResultRow $row) {
    /** @var \Drupal\event\EventInterface $event */
    $event = $this->getEntity($row);
    return Url::fromRoute('event.revision_revert_confirm', ['event' => $event->id(), 'event_revision' => $event->getRevisionId()]);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultLabel() {
    return $this->t('Revert');
  }

}
