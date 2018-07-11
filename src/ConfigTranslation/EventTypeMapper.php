<?php

namespace Drupal\event\ConfigTranslation;

use Drupal\config_translation\ConfigEntityMapper;
use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides a configuration mapper for event types.
 */
class EventTypeMapper extends ConfigEntityMapper {

  /**
   * {@inheritdoc}
   */
  public function setEntity(ConfigEntityInterface $entity) {
    parent::setEntity($entity);

    // Adds the title label to the translation form.
    $event_type = $entity->id();
    $config = $this->configFactory->get("core.base_field_override.event.$event_type.title");
    if (!$config->isNew()) {
      $this->addConfigName($config->getName());
    }
  }

}
