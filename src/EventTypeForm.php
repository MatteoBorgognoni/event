<?php

namespace Drupal\event;

use Drupal\Core\Entity\BundleEntityFormBase;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\language\Entity\ContentLanguageSettings;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form handler for event type forms.
 *
 * @internal
 */
class EventTypeForm extends BundleEntityFormBase {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs the EventTypeForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $type = $this->entity;
    if ($this->operation == 'add') {
      $form['#title'] = $this->t('Add event type');
      $fields = $this->entityManager->getBaseFieldDefinitions('event');
      // Create a event with a fake bundle using the type's UUID so that we can
      // get the default values for workflow settings.
      // @todo Make it possible to get default values without an entity.
      //   https://www.drupal.org/event/2318187
      $event = $this->entityManager->getStorage('event')->create(['type' => $type->uuid()]);
    }
    else {
      $form['#title'] = $this->t('Edit %label event type', ['%label' => $type->label()]);
      $fields = $this->entityManager->getFieldDefinitions('event', $type->id());
      // Create a event to get the current values for workflow settings fields.
      $event = $this->entityManager->getStorage('event')->create(['type' => $type->id()]);
    }

    $form['name'] = [
      '#title' => t('Name'),
      '#type' => 'textfield',
      '#default_value' => $type->label(),
      '#description' => t('The human-readable name of this event type. This text will be displayed as part of the list on the <em>Add event</em> page. This name must be unique.'),
      '#required' => TRUE,
      '#size' => 30,
    ];

    $form['type'] = [
      '#type' => 'machine_name',
      '#default_value' => $type->id(),
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#disabled' => $type->isLocked(),
      '#machine_name' => [
        'exists' => ['Drupal\event\Entity\EventType', 'load'],
        'source' => ['name'],
      ],
      '#description' => t('A unique machine-readable name for this event type. It must only contain lowercase letters, numbers, and underscores. This name will be used for constructing the URL of the %event-add page, in which underscores will be converted into hyphens.', [
        '%event-add' => t('Add event'),
      ]),
    ];

    $form['description'] = [
      '#title' => t('Description'),
      '#type' => 'textarea',
      '#default_value' => $type->getDescription(),
      '#description' => t('This text will be displayed on the <em>Add new event</em> page.'),
    ];

    $form['additional_settings'] = [
      '#type' => 'vertical_tabs',
      '#attached' => [
        'library' => ['event/drupal.event_types'],
      ],
    ];

    $form['submission'] = [
      '#type' => 'details',
      '#title' => t('Submission form settings'),
      '#group' => 'additional_settings',
      '#open' => TRUE,
    ];
    $form['submission']['title_label'] = [
      '#title' => t('Title field label'),
      '#type' => 'textfield',
      '#default_value' => $fields['title']->getLabel(),
      '#required' => TRUE,
    ];
    $form['submission']['preview_mode'] = [
      '#type' => 'radios',
      '#title' => t('Preview before submitting'),
      '#default_value' => $type->getPreviewMode(),
      '#options' => [
        DRUPAL_DISABLED => t('Disabled'),
        DRUPAL_OPTIONAL => t('Optional'),
        DRUPAL_REQUIRED => t('Required'),
      ],
    ];
    $form['submission']['help']  = [
      '#type' => 'textarea',
      '#title' => t('Explanation or submission guidelines'),
      '#default_value' => $type->getHelp(),
      '#description' => t('This text will be displayed at the top of the page when creating or editing event of this type.'),
    ];
    $form['workflow'] = [
      '#type' => 'details',
      '#title' => t('Publishing options'),
      '#group' => 'additional_settings',
    ];
    $workflow_options = [
      'status' => $event->status->value,
      'promote' => $event->promote->value,
      'sticky' => $event->sticky->value,
      'revision' => $type->isNewRevision(),
    ];
    // Prepare workflow options to be used for 'checkboxes' form element.
    $keys = array_keys(array_filter($workflow_options));
    $workflow_options = array_combine($keys, $keys);
    $form['workflow']['options'] = [
      '#type' => 'checkboxes',
      '#title' => t('Default options'),
      '#default_value' => $workflow_options,
      '#options' => [
        'status' => t('Published'),
        'promote' => t('Promoted to front page'),
        'sticky' => t('Sticky at top of lists'),
        'revision' => t('Create new revision'),
      ],
      '#description' => t('Users with the <em>Administer event</em> permission will be able to override these options.'),
    ];
    if ($this->moduleHandler->moduleExists('language')) {
      $form['language'] = [
        '#type' => 'details',
        '#title' => t('Language settings'),
        '#group' => 'additional_settings',
      ];

      $language_configuration = ContentLanguageSettings::loadByEntityTypeBundle('event', $type->id());
      $form['language']['language_configuration'] = [
        '#type' => 'language_configuration',
        '#entity_information' => [
          'entity_type' => 'event',
          'bundle' => $type->id(),
        ],
        '#default_value' => $language_configuration,
      ];
    }
    $form['display'] = [
      '#type' => 'details',
      '#title' => t('Display settings'),
      '#group' => 'additional_settings',
    ];
    $form['display']['display_submitted'] = [
      '#type' => 'checkbox',
      '#title' => t('Display author and date information'),
      '#default_value' => $type->displaySubmitted(),
      '#description' => t('Author username and publish date will be displayed.'),
    ];

    return $this->protectBundleIdElement($form);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = t('Save event type');
    $actions['delete']['#value'] = t('Delete event type');
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $id = trim($form_state->getValue('type'));
    // '0' is invalid, since elsewhere we check it using empty().
    if ($id == '0') {
      $form_state->setErrorByName('type', $this->t("Invalid machine-readable name. Enter a name other than %invalid.", ['%invalid' => $id]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $type = $this->entity;
    $type->setNewRevision($form_state->getValue(['options', 'revision']));
    $type->set('type', trim($type->id()));
    $type->set('name', trim($type->label()));

    $status = $type->save();

    $t_args = ['%name' => $type->label()];

    if ($status == SAVED_UPDATED) {
      drupal_set_message(t('The event type %name has been updated.', $t_args));
    }
    elseif ($status == SAVED_NEW) {
      // TODO Fire event for programmatically install fields (something similar to node_add_body_field)
      drupal_set_message(t('The event type %name has been added.', $t_args));
      $context = array_merge($t_args, ['link' => $type->link($this->t('View'), 'collection')]);
      $this->logger('event')->notice('Added event type %name.', $context);
    }

    $fields = $this->entityManager->getFieldDefinitions('event', $type->id());
    // Update title field definition.
    $title_field = $fields['title'];
    $title_label = $form_state->getValue('title_label');
    if ($title_field->getLabel() != $title_label) {
      $title_field->getConfig($type->id())->setLabel($title_label)->save();
    }
    // Update workflow options.
    // @todo Make it possible to get default values without an entity.
    //   https://www.drupal.org/event/2318187
    $event = $this->entityManager->getStorage('event')->create(['type' => $type->id()]);
    foreach (['status', 'promote', 'sticky'] as $field_name) {
      $value = (bool) $form_state->getValue(['options', $field_name]);
      if ($event->$field_name->value != $value) {
        $fields[$field_name]->getConfig($type->id())->setDefaultValue($value)->save();
      }
    }

    $this->entityManager->clearCachedFieldDefinitions();
    $form_state->setRedirectUrl($type->urlInfo('collection'));
  }

}
