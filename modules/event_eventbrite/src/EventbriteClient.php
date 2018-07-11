<?php

namespace Drupal\event_eventbrite;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Class EventbriteClient.
 */
class EventbriteClient {

  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;
  /**
   * Drupal\Core\Config\ImmutableConfig.
   *
   * @var \\Drupal\Core\Config\ImmutableConfig
   */
  protected $config;
  /**
   * Symfony\Component\HttpFoundation\RequestStack definition.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;
  /**
   * Drupal\Core\Session\AccountProxyInterface definition.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  protected $token = NULL;

  /**
   * Constructs a new EventbriteClient object.
   */
  public function __construct(ConfigFactoryInterface $config_factory, RequestStack $request_stack, AccountProxyInterface $current_user) {
    $this->configFactory = $config_factory;
    $this->requestStack = $request_stack;
    $this->currentUser = $current_user;
    $this->config = $this->configFactory->get('event_eventbrite.settings');

    $mode = $this->config->get('mode');
    switch ($mode) {
      case 'live':
        $token = trim($this->config->get('token'));
        break;
      case 'test':
      default:
        $token = trim($this->config->get('token_test'));
        break;
    }

    if($this->config->get('token')) {
      $this->token = $token;
    }
  }

  public function client() {
    return new HttpClient($this->token);
  }

}
