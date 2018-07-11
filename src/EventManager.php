<?php

namespace Drupal\event;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\EntityFormBuilder;
use Drupal\event_venue\Entity\VenueInterface;
use Drupal\event_content\Entity\EventContentInterface;
use Drupal\event_ticket\Entity\TicketInterface;
use Drupal\event_organizer\Entity\OrganizerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\storage\StorageFactory;
use Drupal\storage\Storage;
use Drupal\Core\TempStore\TempStoreException;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\event\EventInterface;
use Drupal\Core\Database\Connection;

/**
 * Class EventManager.
 */
class EventManager {

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  public $entityTypeManager;
  /**
   * Drupal\Core\Entity\EntityFormBuilder definition.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilder
   */
  public $entityFormBuilder;
  /**
   * Drupal\Core\Database\Connection definition.
   *
   * @var \Drupal\Core\Database\Connection
   */
  public $db;
  /**
   * Symfony\Component\EventDispatcher\EventDispatcherInterface definition.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  
  public $eventDispatcher;
  /**
   * Symfony\Component\Routing\Matcher\RequestMatcherInterface definition.
   *
   * @var \Symfony\Component\Routing\Matcher\RequestMatcherInterface
   */
  public $routerMatcher;
  /**
   * Drupal\Core\Render\RendererInterface definition.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  public $renderer;
  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  public $configFactory;
  /**
   * Symfony\Component\HttpFoundation\RequestStack definition.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  public $requestStack;
  /**
   * Drupal\Core\Routing\CurrentRouteMatch definition.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  public $currentRouteMatch;
  /**
   * Drupal\Core\Session\AccountProxyInterface definition.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  public $currentUser;
  /**
   * Drupal\storage\StorageFactory definition.
   *
   * @var \Drupal\storage\StorageFactory
   */
  public $storageManager;
  /**
   * Drupal\storage\StorageFactory definition.
   *
   * @var \Drupal\storage\Storage
   */
  public $storage;
  /**
   * Drupal\Core\Logger\LoggerChannelFactory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  public $logger;
  /**
   * Constructs a new EventManager object.
   */
  public function __construct(
    EntityTypeManager $entity_type_manager,
    EntityFormBuilder $entity_form_builder,
    Connection $database,
    EventDispatcherInterface $event_dispatcher,
    RequestMatcherInterface $router_matcher,
    RendererInterface $renderer,
    ConfigFactoryInterface $config_factory,
    RequestStack $request_stack,
    CurrentRouteMatch $current_route_match,
    AccountProxyInterface $current_user,
    StorageFactory $storage_manager,
    LoggerChannelFactory $logger_factory
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFormBuilder = $entity_form_builder;
    $this->db = $database;
    $this->eventDispatcher = $event_dispatcher;
    $this->routerMatcher = $router_matcher;
    $this->renderer = $renderer;
    $this->configFactory = $config_factory;
    $this->requestStack = $request_stack;
    $this->currentRouteMatch = $current_route_match;
    $this->currentUser = $current_user;
    $this->storageManager = $storage_manager;
    $this->logger = $logger_factory->get('Event');
    $this->storage = $this->storageManager->get('event');
  }

  public function getConfig($name) {
    /** @var \Drupal\Core\Config\ImmutableConfig $config */
    $config = $this->configFactory->get($name);
    return $config;
  }
 
  public function getEntityStorage($entity_type) {
    /** @var \Drupal\Core\Entity\EntityStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage($entity_type);
    return $storage;
  }
  
  public function getEntityViewBuilder($entity_type) {
    /** @var \Drupal\Core\Entity\EntityViewBuilderInterface $viewBuilder */
    $viewBuilder = $this->entityTypeManager->getViewBuilder($entity_type);
    return $viewBuilder;
  }
  
  public function getEntityForm(EntityInterface $entity, $operation = 'default', $additions = []) {
    $form = $this->entityFormBuilder->getForm($entity, $operation, $additions);
    return $form;
  }
  
  public function getEntityQuery($entity_type) {
    /** @var \Drupal\Core\Entity\Query\QueryInterface */
    $query = $this->getEntityStorage($entity_type)->getQuery();
    return $query;
  }
  
  public function loadEntity($entity_type, $id) {
    try {
      /** @var EntityInterface $entity */
      $entity = $this->entityTypeManager->getStorage($entity_type)->load($id);
      return $entity;
    }
    catch (InvalidPluginDefinitionException $e) {
      $this->log('error', $e->getMessage());
      return NULL;
    }
  }

  public function createEntity($entity_type, ParameterBag $values = NULL, $save = FALSE) {
    try {
      /** @var EntityInterface $entity */
      $entity = $this->getEntityStorage($entity_type)->create($values->all());
      if($save) {
        $entity->save();
      }
      return $entity;
    }
    catch (InvalidPluginDefinitionException $e) {
      $this->log('error', $e->getMessage());
      return NULL;
    }
  }

  public function updateEntity(EntityInterface $entity, ParameterBag $values) {
    try {
      foreach ($values as $fieldName => $fieldValue) {
        $entity->set($fieldName, $fieldValue);
      }
      $entity->save();
      return $entity;
    }
    catch (InvalidPluginDefinitionException $e) {
      $this->log('error', $e->getMessage());
      return NULL;
    }
  }

  public function publishEntity(EntityInterface $entity) {
    $entity->set('status', 1);
    $entity->set('moderation_state', 'published');
    $entity->save();
    return $entity;
  }

  public function unpublishEntity(EntityInterface $entity) {
    $entity->set('status', 0);
    $entity->set('moderation_state', 'draft');
    $entity->save();
    return $entity;
  }
  
  public function log($level, $message, $context = []) {
    $this->logger->log($level, $message, $context);
  }

  public function debug($message, $context) {
    $this->logger->debug($message, $context);
  }

  public function storageSet($key, $value) {
    try {
      $this->storage->set($key, $value);
    }
    catch (TempStoreException $exception) {
      $this->log('error', $exception->getMessage());
    }
  }

  public function storageGet($key) {
    return $this->storage->get($key);
  }

  public function storageGetAll() {
    return $this->storage->getAll();
  }

  public function storageClear() {
    return $this->storage->deleteAll();
  }

}
