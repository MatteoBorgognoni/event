<?php

/**
 * @file
 * Builds placeholder replacement tokens for event-related data.
 */

use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\user\Entity\User;

/**
 * Implements hook_token_info().
 */
function event_token_info() {
  $type = [
    'name' => t('Events'),
    'description' => t('Tokens related to individual content items, or "events".'),
    'needs-data' => 'event',
  ];

  // Core tokens for events.
  $event['eid'] = [
    'name' => t("Event ID"),
    'description' => t('The unique ID of the event item, or "event".'),
  ];
  $event['vid'] = [
    'name' => t("Revision ID"),
    'description' => t("The unique ID of the event's latest revision."),
  ];
  $event['type'] = [
    'name' => t("Event type"),
  ];
  $event['type-name'] = [
    'name' => t("Event type name"),
    'description' => t("The human-readable name of the event type."),
  ];
  $event['title'] = [
    'name' => t("Title"),
  ];
  $event['langcode'] = [
    'name' => t('Language code'),
    'description' => t('The language code of the language the event is written in.'),
  ];
  $event['url'] = [
    'name' => t("URL"),
    'description' => t("The URL of the event."),
  ];
  $event['edit-url'] = [
    'name' => t("Edit URL"),
    'description' => t("The URL of the event's edit page."),
  ];

  // Chained tokens for events.
  $event['created'] = [
    'name' => t("Date created"),
    'type' => 'date',
  ];
  $event['changed'] = [
    'name' => t("Date changed"),
    'description' => t("The date the event was most recently updated."),
    'type' => 'date',
  ];
  $event['author'] = [
    'name' => t("Author"),
    'type' => 'user',
  ];

  return [
    'types' => ['event' => $type],
    'tokens' => ['event' => $event],
  ];
}

/**
 * Implements hook_tokens().
 */
function event_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $token_service = \Drupal::token();

  $url_options = ['absolute' => TRUE];
  if (isset($options['langcode'])) {
    $url_options['language'] = \Drupal::languageManager()->getLanguage($options['langcode']);
    $langcode = $options['langcode'];
  }
  else {
    $langcode = LanguageInterface::LANGCODE_DEFAULT;
  }
  $replacements = [];

  if ($type == 'event' && !empty($data['event'])) {
    /** @var \Drupal\event\EventInterface $event */
    $event = $data['event'];

    foreach ($tokens as $name => $original) {
      switch ($name) {
        // Simple key values on the event.
        case 'eid':
          $replacements[$original] = $event->id();
          break;

        case 'vid':
          $replacements[$original] = $event->getRevisionId();
          break;

        case 'type':
          $replacements[$original] = $event->getType();
          break;

        case 'type-name':
          $type_name = event_get_type_label($event);
          $replacements[$original] = $type_name;
          break;

        case 'title':
          $replacements[$original] = $event->getTitle();
          break;

        case 'langcode':
          $replacements[$original] = $event->language()->getId();
          break;

        case 'url':
          $replacements[$original] = $event->url('canonical', $url_options);
          break;

        case 'edit-url':
          $replacements[$original] = $event->url('edit-form', $url_options);
          break;

        // Default values for the chained tokens handled below.
        case 'author':
          $account = $event->getOwner() ? $event->getOwner() : User::load(0);
          $bubbleable_metadata->addCacheableDependency($account);
          $replacements[$original] = $account->label();
          break;

        case 'created':
          $date_format = DateFormat::load('medium');
          $bubbleable_metadata->addCacheableDependency($date_format);
          $replacements[$original] = format_date($event->getCreatedTime(), 'medium', '', NULL, $langcode);
          break;

        case 'changed':
          $date_format = DateFormat::load('medium');
          $bubbleable_metadata->addCacheableDependency($date_format);
          $replacements[$original] = format_date($event->getChangedTime(), 'medium', '', NULL, $langcode);
          break;
      }
    }

    if ($author_tokens = $token_service->findWithPrefix($tokens, 'author')) {
      $replacements += $token_service->generate('user', $author_tokens, ['user' => $event->getOwner()], $options, $bubbleable_metadata);
    }

    if ($created_tokens = $token_service->findWithPrefix($tokens, 'created')) {
      $replacements += $token_service->generate('date', $created_tokens, ['date' => $event->getCreatedTime()], $options, $bubbleable_metadata);
    }

    if ($changed_tokens = $token_service->findWithPrefix($tokens, 'changed')) {
      $replacements += $token_service->generate('date', $changed_tokens, ['date' => $event->getChangedTime()], $options, $bubbleable_metadata);
    }
  }

  return $replacements;
}
