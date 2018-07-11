<?php

namespace Drupal\event\Cache;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CalculatedCacheContextInterface;
use Drupal\Core\Cache\Context\UserCacheContextBase;

/**
 * Defines the event access view cache context service.
 *
 * Cache context ID: 'user.event_grants' (to vary by all operations' grants).
 * Calculated cache context ID: 'user.event_grants:%operation', e.g.
 * 'user.event_grants:view' (to vary by the view operation's grants).
 *
 * This allows for event access grants-sensitive caching when listing events.
 *
 * @see event_query_event_access_alter()
 * @ingroup event_access
 */
class EventAccessGrantsCacheContext extends UserCacheContextBase implements CalculatedCacheContextInterface {

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t("Event access view grants");
  }

  /**
   * {@inheritdoc}
   */
  public function getContext($operation = NULL) {
    // If the current user either can bypass event access then we don't need to
    // determine the exact event grants for the current user.
    if ($this->user->hasPermission('bypass event access')) {
      return 'all';
    }

    // When no specific operation is specified, check the grants for all three
    // possible operations.
    if ($operation === NULL) {
      $result = [];
      foreach (['view', 'update', 'delete'] as $op) {
        $result[] = $this->checkEventGrants($op);
      }
      return implode('-', $result);
    }
    else {
      return $this->checkEventGrants($operation);
    }
  }

  /**
   * Checks the event grants for the given operation.
   *
   * @param string $operation
   *   The operation to check the event grants for.
   *
   * @return string
   *   The string representation of the cache context.
   */
  protected function checkEventGrants($operation) {
    // When checking the grants for the 'view' operation and the current user
    // has a global view grant (i.e. a view grant for event ID 0) — note that
    // this is automatically the case if no event access modules exist (no
    // hook_event_grants() implementations) then we don't need to determine the
    // exact event view grants for the current user.
    if ($operation === 'view' && event_access_view_all_events($this->user)) {
      return 'view.all';
    }

    $grants = event_access_grants($operation, $this->user);
    $grants_context_parts = [];
    foreach ($grants as $realm => $gids) {
      $grants_context_parts[] = $realm . ':' . implode(',', $gids);
    }
    return $operation . '.' . implode(';', $grants_context_parts);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($operation = NULL) {
    $cacheable_metadata = new CacheableMetadata();

    if (!\Drupal::moduleHandler()->getImplementations('event_grants')) {
      return $cacheable_metadata;
    }

    // The event grants may change if the user is updated. (The max-age is set to
    // zero below, but sites may override this cache context, and change it to a
    // non-zero value. In such cases, this cache tag is needed for correctness.)
    $cacheable_metadata->setCacheTags(['user:' . $this->user->id()]);

    // If the site is using event grants, this cache context can not be
    // optimized.
    return $cacheable_metadata->setCacheMaxAge(0);
  }

}
