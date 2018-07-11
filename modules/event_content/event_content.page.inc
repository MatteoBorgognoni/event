<?php

/**
 * @file
 * Contains event_content.page.inc.
 *
 * Page callback for Event content entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Event content templates.
 *
 * Default template: event_content.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_event_content(array &$variables) {
  // Fetch EventContent Entity Object.
  $event_content = $variables['elements']['#event_content'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
