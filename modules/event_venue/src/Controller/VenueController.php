<?php

namespace Drupal\event_venue\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\event_venue\Entity\VenueInterface;

/**
 * Class VenueController.
 *
 *  Returns responses for Venue routes.
 */
class VenueController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Venue  revision.
   *
   * @param int $venue_revision
   *   The Venue  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($venue_revision) {
    $venue = $this->entityManager()->getStorage('venue')->loadRevision($venue_revision);
    $view_builder = $this->entityManager()->getViewBuilder('venue');

    return $view_builder->view($venue);
  }

  /**
   * Page title callback for a Venue  revision.
   *
   * @param int $venue_revision
   *   The Venue  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($venue_revision) {
    $venue = $this->entityManager()->getStorage('venue')->loadRevision($venue_revision);
    return $this->t('Revision of %title from %date', ['%title' => $venue->label(), '%date' => format_date($venue->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Venue .
   *
   * @param \Drupal\event_venue\Entity\VenueInterface $venue
   *   A Venue  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(VenueInterface $venue) {
    $account = $this->currentUser();
    $langcode = $venue->language()->getId();
    $langname = $venue->language()->getName();
    $languages = $venue->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $venue_storage = $this->entityManager()->getStorage('venue');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $venue->label()]) : $this->t('Revisions for %title', ['%title' => $venue->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all venue revisions") || $account->hasPermission('administer venue entities')));
    $delete_permission = (($account->hasPermission("delete all venue revisions") || $account->hasPermission('administer venue entities')));

    $rows = [];

    $vids = $venue_storage->revisionIds($venue);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\event_venue\VenueInterface $revision */
      $revision = $venue_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $venue->getRevisionId()) {
          $link = $this->l($date, new Url('entity.venue.revision', ['venue' => $venue->id(), 'venue_revision' => $vid]));
        }
        else {
          $link = $venue->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => \Drupal::service('renderer')->renderPlain($username),
              'message' => ['#markup' => $revision->getRevisionLogMessage(), '#allowed_tags' => Xss::getHtmlTagList()],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.venue.translation_revert', ['venue' => $venue->id(), 'venue_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.venue.revision_revert', ['venue' => $venue->id(), 'venue_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.venue.revision_delete', ['venue' => $venue->id(), 'venue_revision' => $vid]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['venue_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
