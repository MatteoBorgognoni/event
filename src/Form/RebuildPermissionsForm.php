<?php

namespace Drupal\event\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Form for rebuilding permissions.
 *
 * @internal
 */
class RebuildPermissionsForm extends ConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'event_configure_rebuild_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to rebuild the permissions on site events?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('system.status');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Rebuild permissions');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return t('This action rebuilds all permissions on site events, and may be a lengthy process. This action cannot be undone.');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    event_access_rebuild(TRUE);
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
