<?php

namespace Drupal\event_organizer\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class OrganizerTypeForm.
 */
class OrganizerTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $organizer_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $organizer_type->label(),
      '#description' => $this->t("Label for the Organizer type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $organizer_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\event_organizer\Entity\OrganizerType::load',
      ],
      '#disabled' => !$organizer_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $organizer_type = $this->entity;
    $status = $organizer_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Organizer type.', [
          '%label' => $organizer_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Organizer type.', [
          '%label' => $organizer_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($organizer_type->toUrl('collection'));
  }

}
