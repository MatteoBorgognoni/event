<?php

namespace Drupal\event\Plugin\views\argument;

use Drupal\user\Plugin\views\argument\Uid;

/**
 * Filter handler to accept a user id to check for events that
 * user posted or created a revision on.
 *
 * @ViewsArgument("event_uid_revision")
 */
class UidRevision extends Uid {

  public function query($group_by = FALSE) {
    $this->ensureMyTable();
    $placeholder = $this->placeholder();
    $this->query->addWhereExpression(0, "$this->tableAlias.uid = $placeholder OR ((SELECT COUNT(DISTINCT vid) FROM {event_revision} nr WHERE nr.revision_uid = $placeholder AND nr.eid = $this->tableAlias.eid) > 0)", [$placeholder => $this->argument]);
  }

}
