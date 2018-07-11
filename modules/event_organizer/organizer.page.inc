<?php

/**
 * @file
 * Contains organizer.page.inc.
 *
 * Page callback for Organizer entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Organizer templates.
 *
 * Default template: organizer.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_organizer(array &$variables) {
  // Fetch Organizer Entity Object.
  $organizer = $variables['elements']['#organizer'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
