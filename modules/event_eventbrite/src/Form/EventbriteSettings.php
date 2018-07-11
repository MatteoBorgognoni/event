<?php

namespace Drupal\event_eventbrite\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\event\EventManager;

/**
 * Class EventbriteSettings.
 */
class EventbriteSettings extends ConfigFormBase {

  /**
   * Drupal\event\EventManager definition.
   *
   * @var \Drupal\event\EventManager
   */
  protected $eventManager;
  /**
   * Constructs a new EventbriteSettings object.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EventManager $event_manager
  ) {
    parent::__construct($config_factory);
    $this->eventManager = $event_manager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('event.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'event_eventbrite.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'eventbrite_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('event_eventbrite.settings');

    $form['mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Mode'),
      '#name' => 'mode',
      '#options' => [
        'test' => 'test',
        'live' => 'live',
      ],
      '#default_value' => $config->get('mode'),
    ];

    $form['token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Token'),
      '#maxlength' => 128,
      '#size' => 64,
      '#default_value' => $config->get('token'),
    ];

    $form['token_test'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Test Token'),
      '#maxlength' => 128,
      '#size' => 64,
      '#default_value' => $config->get('token_test'),
    ];

    $form['action'] = [
      '#type' => 'select',
      '#title' => $this->t('Action to test'),
      '#options' => [
        'create' => 'create',
        'update' => 'update',
        'publish' => 'publish',
        'unpublish' => 'unpublish',
      ],
      '#default_value' => $config->get('action'),
      '#states' => [
        'visible' => [
          ':input[name="mode"]' => ['value' => 'test'],
        ],
        'required' => [
          ':input[name="mode"]' => ['value' => 'test'],
        ],
      ],
    ];

    $form['test_event'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Test Event Url'),
      '#maxlength' => 128,
      '#size' => 64,
      '#default_value' => $config->get('test_event'),
      '#states' => [
        'visible' => [
          ':input[name="mode"]' => ['value' => 'test'],
        ],
        'required' => [
          ':input[name="mode"]' => ['value' => 'test'],
        ],
      ],
    ];

    $form['actions']['delete_data'] = [
      '#type' => 'submit',
      '#submit' => ['::deleteTestData'],
      '#name' => 'delete_data',
      '#value' => 'Delete test data',
      '#weight' => 1000
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('event_eventbrite.settings')
      ->set('mode', $form_state->getValue('mode'))
      ->set('token', $form_state->getValue('token'))
      ->set('token_test', $form_state->getValue('token_test'))
      ->set('action', $form_state->getValue('action'))
      ->set('test_event', $form_state->getValue('test_event'))
      ->save();
  }

  public function deleteTestData(array &$form, FormStateInterface $form_state) {
    $types = [
      'event',
      'event_content',
      'ticket',
      'venue',
      'organizer'
    ];
    foreach ($types as $entity_type) {
      $this->deleteData($entity_type);
    }
  }

  public function deleteData($entityType) {
    $db = \Drupal::database();
    $id_key = \Drupal::entityTypeManager()->getDefinition($entityType)->getKey('id');
    $ids = $db->select($entityType, 'e')->fields('e', [$id_key])->execute()->fetchAllKeyed(0,0);
    ksm($entityType, $ids);
    $controller = \Drupal::entityTypeManager()->getStorage($entityType);
    $entities = $controller->loadMultiple($ids);
    $controller->delete($entities);
  }

}
