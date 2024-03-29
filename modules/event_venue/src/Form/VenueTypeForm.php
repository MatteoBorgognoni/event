<?php

namespace Drupal\event_venue\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class VenueTypeForm.
 */
class VenueTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $venue_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $venue_type->label(),
      '#description' => $this->t("Label for the Venue type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $venue_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\event_venue\Entity\VenueType::load',
      ],
      '#disabled' => !$venue_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $venue_type = $this->entity;
    $status = $venue_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Venue type.', [
          '%label' => $venue_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Venue type.', [
          '%label' => $venue_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($venue_type->toUrl('collection'));
  }

}
