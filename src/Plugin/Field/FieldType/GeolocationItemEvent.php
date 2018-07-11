<?php

namespace Drupal\event\Plugin\Field\FieldType;


use Drupal\geolocation\Plugin\Field\FieldType\GeolocationItem;

/**
 * Plugin implementation of the 'geolocation_event' field type.
 *
 * @FieldType(
 *   id = "geolocation_event",
 *   label = @Translation("Geolocation Event"),
 *   description = @Translation("This field stores location data (lat, lng)."),
 *   default_widget = "geolocation_latlng",
 *   default_formatter = "geolocation_latlng"
 * )
 */
class GeolocationItemEvent extends GeolocationItem {

  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE) {
    if (isset($values)) {
      $values += [
        'data' => [],
      ];
    }
    // Unserialize the values.
    // @todo The storage controller should take care of this, see
    //   SqlContentEntityStorage::loadFieldItems, see
    //   https://www.drupal.org/node/2232427
    if (is_string($values['data'])) {
      $values['data'] = unserialize($values['data']);
    }
    parent::setValue($values, $notify);
  }


}
