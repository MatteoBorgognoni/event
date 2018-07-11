<?php

namespace Drupal\event\Form;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides a event deletion confirmation form.
 *
 * @internal
 */
class DeleteMultiple extends ConfirmFormBase {

  /**
   * The array of events to delete.
   *
   * @var string[][]
   */
  protected $eventInfo = [];

  /**
   * The tempstore factory.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * The event storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * Constructs a DeleteMultiple form object.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   * @param \Drupal\Core\Entity\EntityManagerInterface $manager
   *   The entity manager.
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory, EntityManagerInterface $manager) {
    $this->tempStoreFactory = $temp_store_factory;
    $this->storage = $manager->getStorage('event');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'event_multiple_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->formatPlural(count($this->eventInfo), 'Are you sure you want to delete this item?', 'Are you sure you want to delete these items?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('system.admin_content');
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
  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->eventInfo = $this->tempStoreFactory->get('event_multiple_delete_confirm')->get(\Drupal::currentUser()->id());
    if (empty($this->eventInfo)) {
      return new RedirectResponse($this->getCancelUrl()->setAbsolute()->toString());
    }
    /** @var \Drupal\event\EventInterface[] $events */
    $events = $this->storage->loadMultiple(array_keys($this->eventInfo));

    $items = [];
    foreach ($this->eventInfo as $id => $langcodes) {
      foreach ($langcodes as $langcode) {
        $event = $events[$id]->getTranslation($langcode);
        $key = $id . ':' . $langcode;
        $default_key = $id . ':' . $event->getUntranslated()->language()->getId();

        // If we have a translated entity we build a nested list of translations
        // that will be deleted.
        $languages = $event->getTranslationLanguages();
        if (count($languages) > 1 && $event->isDefaultTranslation()) {
          $names = [];
          foreach ($languages as $translation_langcode => $language) {
            $names[] = $language->getName();
            unset($items[$id . ':' . $translation_langcode]);
          }
          $items[$default_key] = [
            'label' => [
              '#markup' => $this->t('@label (Original translation) - <em>The following event translations will be deleted:</em>', ['@label' => $event->label()]),
            ],
            'deleted_translations' => [
              '#theme' => 'item_list',
              '#items' => $names,
            ],
          ];
        }
        elseif (!isset($items[$default_key])) {
          $items[$key] = $event->label();
        }
      }
    }

    $form['events'] = [
      '#theme' => 'item_list',
      '#items' => $items,
    ];
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('confirm') && !empty($this->eventInfo)) {
      $total_count = 0;
      $delete_events = [];
      /** @var \Drupal\Core\Entity\ContentEntityInterface[][] $delete_translations */
      $delete_translations = [];
      /** @var \Drupal\event\EventInterface[] $events */
      $events = $this->storage->loadMultiple(array_keys($this->eventInfo));

      foreach ($this->eventInfo as $id => $langcodes) {
        foreach ($langcodes as $langcode) {
          $event = $events[$id]->getTranslation($langcode);
          if ($event->isDefaultTranslation()) {
            $delete_events[$id] = $event;
            unset($delete_translations[$id]);
            $total_count += count($event->getTranslationLanguages());
          }
          elseif (!isset($delete_events[$id])) {
            $delete_translations[$id][] = $event;
          }
        }
      }

      if ($delete_events) {
        $this->storage->delete($delete_events);
        $this->logger('events')->notice('Deleted @count events.', ['@count' => count($delete_events)]);
      }

      if ($delete_translations) {
        $count = 0;
        foreach ($delete_translations as $id => $translations) {
          $event = $events[$id]->getUntranslated();
          foreach ($translations as $translation) {
            $event->removeTranslation($translation->language()->getId());
          }
          $event->save();
          $count += count($translations);
        }
        if ($count) {
          $total_count += $count;
          $this->logger('events')->notice('Deleted @count event translations.', ['@count' => $count]);
        }
      }

      if ($total_count) {
        drupal_set_message($this->formatPlural($total_count, 'Deleted 1 event.', 'Deleted @count events.'));
      }

      $this->tempStoreFactory->get('event_multiple_delete_confirm')->delete(\Drupal::currentUser()->id());
    }

    $form_state->setRedirect('system.admin_content');
  }

}
