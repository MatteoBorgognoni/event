<?php

namespace Drupal\event\Plugin\Field\FieldType;

use Drupal\Core\Field\Plugin\Field\FieldType\StringItem;

/**
 * Plugin implementation of the 'registration_button' field type.
 *
 * @FieldType(
 *   id = "registration_button",
 *   label = @Translation("Registration Button"),
 *   description = @Translation("Generic button for event registration"),
 *   default_widget = "string_textfield",
 *   default_formatter = "registration_button_default",
 * )
 */
class RegistrationButton extends StringItem {


}
