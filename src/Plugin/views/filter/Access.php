<?php

namespace Drupal\event\Plugin\views\filter;

use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\FilterPluginBase;

/**
 * Filter by event_access records.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("event_access")
 */
class Access extends FilterPluginBase {

  public function adminSummary() {}
  protected function operatorForm(&$form, FormStateInterface $form_state) {}
  public function canExpose() {
    return FALSE;
  }

  /**
   * See _event_access_where_sql() for a non-views query based implementation.
   */
  public function query() {
    $account = $this->view->getUser();
    if (!$account->hasPermission('bypass event access')) {
      $table = $this->ensureMyTable();
      $grants = new Condition('OR');
      foreach (event_access_grants('view', $account) as $realm => $gids) {
        foreach ($gids as $gid) {
          $grants->condition((new Condition('AND'))
            ->condition($table . '.gid', $gid)
            ->condition($table . '.realm', $realm)
          );
        }
      }

      $this->query->addWhere('AND', $grants);
      $this->query->addWhere('AND', $table . '.grant_view', 1, '>=');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    $contexts = parent::getCacheContexts();

    $contexts[] = 'user.event_grants:view';

    return $contexts;
  }

}
