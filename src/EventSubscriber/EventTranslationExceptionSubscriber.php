<?php

namespace Drupal\event\EventSubscriber;

use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\ParamConverter\ParamNotConvertedException;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Redirect event translations that have been consolidated by migration.
 *
 * If we migrated event translations from Drupal 6 or 7, these events are now
 * combined with their source language event. Since there still might be
 * references to the URLs of these now consolidated events, this service catches
 * the 404s and try to redirect them to the right event in the right language.
 *
 * The mapping of the old eids to the new ones is made by the
 * EventTranslationMigrateSubscriber class during the migration and is stored
 * in the "event_translation_redirect" key/value collection.
 *
 * @see \Drupal\event\EventServiceProvider
 * @see \Drupal\event\EventSubscriber\EventTranslationMigrateSubscriber
 */
class EventTranslationExceptionSubscriber implements EventSubscriberInterface {

  /**
   * The key value factory.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueFactoryInterface
   */
  protected $keyValue;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The URL generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Constructs the EventTranslationExceptionSubscriber.
   *
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface $key_value
   *   The key value factory.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The URL generator.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(KeyValueFactoryInterface $key_value, LanguageManagerInterface $language_manager, UrlGeneratorInterface $url_generator, StateInterface $state) {
    $this->keyValue = $key_value;
    $this->languageManager = $language_manager;
    $this->urlGenerator = $url_generator;
    $this->state = $state;
  }

  /**
   * Redirects not found event translations using the key value collection.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
   *   The exception event.
   */
  public function onException(GetResponseForExceptionEvent $event) {
    $exception = $event->getException();

    // If this is not a 404, we don't need to check for a redirection.
    if (!($exception instanceof NotFoundHttpException)) {
      return;
    }

    $previous_exception = $exception->getPrevious();
    if ($previous_exception instanceof ParamNotConvertedException) {
      $route_name = $previous_exception->getRouteName();
      $parameters = $previous_exception->getRawParameters();
      if ($route_name === 'entity.event.canonical' && isset($parameters['event'])) {
        // If the event_translation_redirect state is not set, we don't need to check
        // for a redirection.
        if (!$this->state->get('event_translation_redirect')) {
          return;
        }
        $old_eid = $parameters['event'];
        $collection = $this->keyValue->get('event_translation_redirect');
        if ($old_eid && $value = $collection->get($old_eid)) {
          list($eid, $langcode) = $value;
          $language = $this->languageManager->getLanguage($langcode);
          $url = $this->urlGenerator->generateFromRoute('entity.event.canonical', ['event' => $eid], ['language' => $language]);
          $response = new RedirectResponse($url, 301);
          $event->setResponse($response);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];

    $events[KernelEvents::EXCEPTION] = ['onException'];

    return $events;
  }

}
