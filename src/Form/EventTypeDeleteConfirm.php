<?php

namespace Drupal\event\Form;

use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for event type deletion.
 *
 * @internal
 */
class EventTypeDeleteConfirm extends EntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $num_events = $this->entityTypeManager->getStorage('event')->getQuery()
      ->condition('type', $this->entity->id())
      ->count()
      ->execute();
    if ($num_events) {
      $caption = '<p>' . $this->formatPlural($num_events, '%type is used by 1 piece of events on your site. You can not remove this event type until you have removed all of the %type content.', '%type is used by @count pieces of content on your site. You may not remove %type until you have removed all of the %type content.', ['%type' => $this->entity->label()]) . '</p>';
      $form['#title'] = $this->getQuestion();
      $form['description'] = ['#markup' => $caption];
      return $form;
    }

    return parent::buildForm($form, $form_state);
  }

}
