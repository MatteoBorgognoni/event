<?php

namespace Drupal\event\Plugin\views\field;

@trigger_error('Drupal\event\Plugin\views\field\Path is deprecated in Drupal 8.5.0 and will be removed before Drupal 9.0.0. Use @ViewsField("entity_link") with \'output_url_as_text\' set.', E_USER_DEPRECATED);

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;

/**
 * Field handler to present the path to the event.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("event_path")
 *
 * @deprecated in Drupal 8.5.x, will be removed before Drupal 9.0.0.
 *  Use @ViewsField("entity_link") with 'output_url_as_text' set.
 */
class Path extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);

    $this->additional_fields['eid'] = 'eid';
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['absolute'] = ['default' => FALSE];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $form['absolute'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use absolute link (begins with "http://")'),
      '#default_value' => $this->options['absolute'],
      '#description' => $this->t('Enable this option to output an absolute link. Required if you want to use the path as a link destination (as in "output this field as a link" above).'),
      '#fieldset' => 'alter',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensureMyTable();
    $this->addAdditionalFields();
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $eid = $this->getValue($values, 'eid');
    return [
      '#markup' => \Drupal::url('entity.event.canonical', ['event' => $eid], ['absolute' => $this->options['absolute']]),
    ];
  }

}
