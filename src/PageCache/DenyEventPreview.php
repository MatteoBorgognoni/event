<?php

namespace Drupal\event\PageCache;

use Drupal\Core\PageCache\ResponsePolicyInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cache policy for event preview page.
 *
 * This policy rule denies caching of responses generated by the
 * entity.event.preview route.
 */
class DenyEventPreview implements ResponsePolicyInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs a deny event preview page cache policy.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   */
  public function __construct(RouteMatchInterface $route_match) {
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public function check(Response $response, Request $request) {
    if ($this->routeMatch->getRouteName() === 'entity.event.preview') {
      return static::DENY;
    }
  }

}