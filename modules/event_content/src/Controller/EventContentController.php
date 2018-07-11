<?php

namespace Drupal\event_content\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\event_content\Entity\EventContentInterface;

/**
 * Class EventContentController.
 *
 *  Returns responses for Event content routes.
 */
class EventContentController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Event content  revision.
   *
   * @param int $event_content_revision
   *   The Event content  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($event_content_revision) {
    $event_content = $this->entityManager()->getStorage('event_content')->loadRevision($event_content_revision);
    $view_builder = $this->entityManager()->getViewBuilder('event_content');

    return $view_builder->view($event_content);
  }

  /**
   * Page title callback for a Event content  revision.
   *
   * @param int $event_content_revision
   *   The Event content  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($event_content_revision) {
    $event_content = $this->entityManager()->getStorage('event_content')->loadRevision($event_content_revision);
    return $this->t('Revision of %title from %date', ['%title' => $event_content->label(), '%date' => format_date($event_content->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Event content .
   *
   * @param \Drupal\event_content\Entity\EventContentInterface $event_content
   *   A Event content  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(EventContentInterface $event_content) {
    $account = $this->currentUser();
    $langcode = $event_content->language()->getId();
    $langname = $event_content->language()->getName();
    $languages = $event_content->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $event_content_storage = $this->entityManager()->getStorage('event_content');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $event_content->label()]) : $this->t('Revisions for %title', ['%title' => $event_content->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all event content revisions") || $account->hasPermission('administer event content entities')));
    $delete_permission = (($account->hasPermission("delete all event content revisions") || $account->hasPermission('administer event content entities')));

    $rows = [];

    $vids = $event_content_storage->revisionIds($event_content);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\event_content\EventContentInterface $revision */
      $revision = $event_content_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $event_content->getRevisionId()) {
          $link = $this->l($date, new Url('entity.event_content.revision', ['event_content' => $event_content->id(), 'event_content_revision' => $vid]));
        }
        else {
          $link = $event_content->link($date);
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
              Url::fromRoute('entity.event_content.translation_revert', ['event_content' => $event_content->id(), 'event_content_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.event_content.revision_revert', ['event_content' => $event_content->id(), 'event_content_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.event_content.revision_delete', ['event_content' => $event_content->id(), 'event_content_revision' => $vid]),
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

    $build['event_content_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
