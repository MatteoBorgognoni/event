<?php

namespace Drupal\event\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Plugin\Field\FieldFormatter\StringFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\SortArray;
use Drupal\donation\DonationWrapper;


/**
 * Plugin implementation of the 'registration_button_default' formatter.
 *
 * @FieldFormatter(
 *   id = "registration_button_default",
 *   label = @Translation("Registration Button"),
 *   field_types = {
 *     "registration_button"
 *   }
 * )
 */
class RegistrationButtonDefault extends StringFormatter {
  


}
