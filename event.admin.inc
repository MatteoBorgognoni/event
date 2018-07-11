<?php

/**
 * @file
 * Content administration and module settings user interface.
 */

use Drupal\event\EventInterface;

/**
 * Updates all events in the passed-in array with the passed-in field values.
 *
 * IMPORTANT NOTE: This function is intended to work when called from a form
 * submission handler. Calling it outside of the form submission process may not
 * work correctly.
 *
 * @param array $events
 *   Array of event eids or events to update.
 * @param array $updates
 *   Array of key/value pairs with event field names and the value to update that
 *   field to.
 * @param string $langcode
 *   (optional) The language updates should be applied to. If none is specified
 *   all available languages are processed.
 * @param bool $load
 *   (optional) TRUE if $events contains an array of event IDs to be loaded, FALSE
 *   if it contains fully loaded events. Defaults to FALSE.
 * @param bool $revisions
 *   (optional) TRUE if $events contains an array of revision IDs instead of
 *   event IDs. Defaults to FALSE; will be ignored if $load is FALSE.
 */
function event_mass_update(array $events, array $updates, $langcode = NULL, $load = FALSE, $revisions = FALSE) {
  // We use batch processing to prevent timeout when updating a large number
  // of events.
  if (count($events) > 10) {
    $batch = [
      'operations' => [
        ['_event_mass_update_batch_process', [$events, $updates, $langcode, $load, $revisions]]
      ],
      'finished' => '_event_mass_update_batch_finished',
      'title' => t('Processing'),
      // We use a single multi-pass operation, so the default
      // 'Remaining x of y operations' message will be confusing here.
      'progress_message' => '',
      'error_message' => t('The update has encountered an error.'),
      // The operations do not live in the .module file, so we need to
      // tell the batch engine which file to load before calling them.
      'file' => drupal_get_path('module', 'event') . '/event.admin.inc',
    ];
    batch_set($batch);
  }
  else {
    $storage = \Drupal::entityTypeManager()->getStorage('event');
    if ($load && !$revisions) {
      $events = $storage->loadMultiple($events);
    }
    foreach ($events as $event) {
      if ($load && $revisions) {
        $event = $storage->loadRevision($event);
      }
      _event_mass_update_helper($event, $updates, $langcode);
    }
    drupal_set_message(t('The update has been performed.'));
  }
}

/**
 * Updates individual events when fewer than 10 are queued.
 *
 * @param \Drupal\event\EventInterface $event
 *   A event to update.
 * @param array $updates
 *   Associative array of updates.
 * @param string $langcode
 *   (optional) The language updates should be applied to. If none is specified
 *   all available languages are processed.
 *
 * @return \Drupal\event\EventInterface
 *   An updated event object.
 *
 * @see event_mass_update()
 */
function _event_mass_update_helper(EventInterface $event, array $updates, $langcode = NULL) {
  $langcodes = isset($langcode) ? [$langcode] : array_keys($event->getTranslationLanguages());
  // For efficiency manually save the original event before applying any changes.
  $event->original = clone $event;
  foreach ($langcodes as $langcode) {
    foreach ($updates as $name => $value) {
      $event->getTranslation($langcode)->$name = $value;
    }
  }
  $event->save();
  return $event;
}

/**
 * Implements callback_batch_operation().
 *
 * Executes a batch operation for event_mass_update().
 *
 * @param array $events
 *   An array of event IDs.
 * @param array $updates
 *   Associative array of updates.
 * @param string $langcode
 *   The language updates should be applied to. If none is specified all
 *   available languages are processed.
 * @param bool $load
 *   TRUE if $events contains an array of event IDs to be loaded, FALSE if it
 *   contains fully loaded events.
 * @param bool $revisions
 *   (optional) TRUE if $events contains an array of revision IDs instead of
 *   event IDs. Defaults to FALSE; will be ignored if $load is FALSE.
 * @param array|\ArrayAccess $context
 *   An array of contextual key/values.
 */
function _event_mass_update_batch_process(array $events, array $updates, $langcode, $load, $revisions, &$context) {
  if (!isset($context['sandbox']['progress'])) {
    $context['sandbox']['progress'] = 0;
    $context['sandbox']['max'] = count($events);
    $context['sandbox']['events'] = $events;
  }

  // Process events by groups of 5.
  $storage = \Drupal::entityTypeManager()->getStorage('event');
  $count = min(5, count($context['sandbox']['events']));
  for ($i = 1; $i <= $count; $i++) {
    // For each eid, load the event, reset the values, and save it.
    $event = array_shift($context['sandbox']['events']);
    if ($load) {
      $event = $revisions ?
        $storage->loadRevision($event) : $storage->load($event);
    }
    $event = _event_mass_update_helper($event, $updates, $langcode);

    // Store result for post-processing in the finished callback.
    $context['results'][] = \Drupal::l($event->label(), $event->urlInfo());

    // Update our progress information.
    $context['sandbox']['progress']++;
  }

  // Inform the batch engine that we are not finished,
  // and provide an estimation of the completion level we reached.
  if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
    $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
  }
}

/**
 * Implements callback_batch_finished().
 *
 * Reports the 'finished' status of batch operation for event_mass_update().
 *
 * @param bool $success
 *   A boolean indicating whether the batch mass update operation successfully
 *   concluded.
 * @param string[] $results
 *   An array of rendered links to events updated via the batch mode process.
 * @param array $operations
 *   An array of function calls (not used in this function).
 *
 * @see _event_mass_update_batch_process()
 */
function _event_mass_update_batch_finished($success, $results, $operations) {
  if ($success) {
    drupal_set_message(t('The update has been performed.'));
  }
  else {
    drupal_set_message(t('An error occurred and processing did not complete.'), 'error');
    $message = \Drupal::translation()->formatPlural(count($results), '1 item successfully processed:', '@count items successfully processed:');
    $item_list = [
      '#theme' => 'item_list',
      '#items' => $results,
    ];
    $message .= \Drupal::service('renderer')->render($item_list);
    drupal_set_message($message);
  }
}
