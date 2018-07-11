<?php

namespace Drupal\event;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;
use Drupal\event\EventSubscriber\EventTranslationExceptionSubscriber;
use Drupal\event\EventSubscriber\EventTranslationMigrateSubscriber;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers services in the container.
 */
class EventServiceProvider implements ServiceProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    // Register the event.event_translation_migrate service in the container if
    // the migrate and language modules are enabled.
    $modules = $container->getParameter('container.modules');
    if (isset($modules['migrate']) && isset($modules['language'])) {
      $container->register('event.event_translation_migrate', EventTranslationMigrateSubscriber::class)
        ->addTag('event_subscriber')
        ->addArgument(new Reference('keyvalue'))
        ->addArgument(new Reference('state'));
    }

    // Register the event.event_translation_exception service in the container if
    // the language module is enabled.
    if (isset($modules['language'])) {
      $container->register('event.event_translation_exception', EventTranslationExceptionSubscriber::class)
        ->addTag('event_subscriber')
        ->addArgument(new Reference('keyvalue'))
        ->addArgument(new Reference('language_manager'))
        ->addArgument(new Reference('url_generator'))
        ->addArgument(new Reference('state'));
    }
  }

}
