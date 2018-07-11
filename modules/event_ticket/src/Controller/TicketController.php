<?php

namespace Drupal\event_ticket\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\event_ticket\Entity\TicketInterface;

/**
 * Class TicketController.
 *
 *  Returns responses for Ticket routes.
 */
class TicketController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Ticket  revision.
   *
   * @param int $ticket_revision
   *   The Ticket  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($ticket_revision) {
    $ticket = $this->entityManager()->getStorage('ticket')->loadRevision($ticket_revision);
    $view_builder = $this->entityManager()->getViewBuilder('ticket');

    return $view_builder->view($ticket);
  }

  /**
   * Page title callback for a Ticket  revision.
   *
   * @param int $ticket_revision
   *   The Ticket  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($ticket_revision) {
    $ticket = $this->entityManager()->getStorage('ticket')->loadRevision($ticket_revision);
    return $this->t('Revision of %title from %date', ['%title' => $ticket->label(), '%date' => format_date($ticket->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Ticket .
   *
   * @param \Drupal\event_ticket\Entity\TicketInterface $ticket
   *   A Ticket  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(TicketInterface $ticket) {
    $account = $this->currentUser();
    $langcode = $ticket->language()->getId();
    $langname = $ticket->language()->getName();
    $languages = $ticket->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $ticket_storage = $this->entityManager()->getStorage('ticket');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $ticket->label()]) : $this->t('Revisions for %title', ['%title' => $ticket->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all ticket revisions") || $account->hasPermission('administer ticket entities')));
    $delete_permission = (($account->hasPermission("delete all ticket revisions") || $account->hasPermission('administer ticket entities')));

    $rows = [];

    $vids = $ticket_storage->revisionIds($ticket);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\event_ticket\TicketInterface $revision */
      $revision = $ticket_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $ticket->getRevisionId()) {
          $link = $this->l($date, new Url('entity.ticket.revision', ['ticket' => $ticket->id(), 'ticket_revision' => $vid]));
        }
        else {
          $link = $ticket->link($date);
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
              Url::fromRoute('entity.ticket.translation_revert', ['ticket' => $ticket->id(), 'ticket_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.ticket.revision_revert', ['ticket' => $ticket->id(), 'ticket_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.ticket.revision_delete', ['ticket' => $ticket->id(), 'ticket_revision' => $vid]),
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

    $build['ticket_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
