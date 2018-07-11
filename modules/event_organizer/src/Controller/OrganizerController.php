<?php

namespace Drupal\event_organizer\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\event_organizer\Entity\OrganizerInterface;

/**
 * Class OrganizerController.
 *
 *  Returns responses for Organizer routes.
 */
class OrganizerController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Organizer  revision.
   *
   * @param int $organizer_revision
   *   The Organizer  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($organizer_revision) {
    $organizer = $this->entityManager()->getStorage('organizer')->loadRevision($organizer_revision);
    $view_builder = $this->entityManager()->getViewBuilder('organizer');

    return $view_builder->view($organizer);
  }

  /**
   * Page title callback for a Organizer  revision.
   *
   * @param int $organizer_revision
   *   The Organizer  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($organizer_revision) {
    $organizer = $this->entityManager()->getStorage('organizer')->loadRevision($organizer_revision);
    return $this->t('Revision of %title from %date', ['%title' => $organizer->label(), '%date' => format_date($organizer->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Organizer .
   *
   * @param \Drupal\event_organizer\Entity\OrganizerInterface $organizer
   *   A Organizer  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(OrganizerInterface $organizer) {
    $account = $this->currentUser();
    $langcode = $organizer->language()->getId();
    $langname = $organizer->language()->getName();
    $languages = $organizer->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $organizer_storage = $this->entityManager()->getStorage('organizer');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $organizer->label()]) : $this->t('Revisions for %title', ['%title' => $organizer->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all organizer revisions") || $account->hasPermission('administer organizer entities')));
    $delete_permission = (($account->hasPermission("delete all organizer revisions") || $account->hasPermission('administer organizer entities')));

    $rows = [];

    $vids = $organizer_storage->revisionIds($organizer);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\event_organizer\OrganizerInterface $revision */
      $revision = $organizer_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $organizer->getRevisionId()) {
          $link = $this->l($date, new Url('entity.organizer.revision', ['organizer' => $organizer->id(), 'organizer_revision' => $vid]));
        }
        else {
          $link = $organizer->link($date);
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
              Url::fromRoute('entity.organizer.translation_revert', ['organizer' => $organizer->id(), 'organizer_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.organizer.revision_revert', ['organizer' => $organizer->id(), 'organizer_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.organizer.revision_delete', ['organizer' => $organizer->id(), 'organizer_revision' => $vid]),
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

    $build['organizer_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
