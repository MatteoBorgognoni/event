<?php

namespace Drupal\event\Plugin\views\field;

use Drupal\Core\Url;
use Drupal\views\ResultRow;

/**
 * Field handler to present link to delete a event revision.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("event_revision_link_delete")
 */
class RevisionLinkDelete extends RevisionLink {

  /**
   * {@inheritdoc}
   */
  protected function getUrlInfo(ResultRow $row) {
    /** @var \Drupal\event\EventInterface $event */
    $event = $this->getEntity($row);
    return Url::fromRoute('event.revision_delete_confirm', ['event' => $event->id(), 'event_revision' => $event->getRevisionId()]);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultLabel() {
    return $this->t('Delete');
  }

}
