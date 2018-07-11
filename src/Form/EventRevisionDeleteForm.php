<?php

namespace Drupal\event\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for reverting a event revision.
 *
 * @internal
 */
class EventRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The event revision.
   *
   * @var \Drupal\event\EventInterface
   */
  protected $revision;

  /**
   * The event storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $eventStorage;

  /**
   * The event type storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $eventTypeStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a new EventRevisionDeleteForm.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $event_storage
   *   The event storage.
   * @param \Drupal\Core\Entity\EntityStorageInterface $event_type_storage
   *   The event type storage.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(EntityStorageInterface $event_storage, EntityStorageInterface $event_type_storage, Connection $connection) {
    $this->eventStorage = $event_storage;
    $this->eventTypeStorage = $event_type_storage;
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $entity_manager = $container->get('entity.manager');
    return new static(
      $entity_manager->getStorage('event'),
      $entity_manager->getStorage('event_type'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'event_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to delete the revision from %revision-date?', ['%revision-date' => format_date($this->revision->getRevisionCreationTime())]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.event.version_history', ['event' => $this->revision->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $event_revision = NULL) {
    $this->revision = $this->eventStorage->loadRevision($event_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->eventStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('events')->notice('@type: deleted %title revision %revision.', ['@type' => $this->revision->bundle(), '%title' => $this->revision->label(), '%revision' => $this->revision->getRevisionId()]);
    $event_type = $this->eventTypeStorage->load($this->revision->bundle())->label();
    drupal_set_message(t('Revision from %revision-date of @type %title has been deleted.', ['%revision-date' => format_date($this->revision->getRevisionCreationTime()), '@type' => $event_type, '%title' => $this->revision->label()]));
    $form_state->setRedirect(
      'entity.event.canonical',
      ['event' => $this->revision->id()]
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {event_field_revision} WHERE eid = :eid', [':eid' => $this->revision->id()])->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.event.version_history',
        ['event' => $this->revision->id()]
      );
    }
  }

}
