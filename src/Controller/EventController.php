<?php

namespace Drupal\event\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\event\EventStorageInterface;
use Drupal\event\EventTypeInterface;
use Drupal\event\EventInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Event routes.
 */
class EventController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a EventController object.
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(DateFormatterInterface $date_formatter, RendererInterface $renderer) {
    $this->dateFormatter = $date_formatter;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter'),
      $container->get('renderer')
    );
  }

  /**
   * Displays add content links for available content types.
   *
   * Redirects to event/add/[type] if only one content type is available.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *   A render array for a list of the event types that can be added; however,
   *   if there is only one event type defined for the site, the function
   *   will return a RedirectResponse to the event add page for that one event
   *   type.
   */
  public function addPage() {
    $build = [
      '#theme' => 'event_add_list',
      '#cache' => [
        'tags' => $this->entityManager()->getDefinition('event_type')->getListCacheTags(),
      ],
    ];

    $content = [];

    // Only use event types the user has access to.
    foreach ($this->entityManager()->getStorage('event_type')->loadMultiple() as $type) {
      $access = $this->entityManager()->getAccessControlHandler('event')->createAccess($type->id(), NULL, [], TRUE);
      if ($access->isAllowed()) {
        $content[$type->id()] = $type;
      }
      $this->renderer->addCacheableDependency($build, $access);
    }

    // Bypass the event/add listing if only one content type is available.
    if (count($content) == 1) {
      $type = array_shift($content);
      return $this->redirect('event.add', ['event_type' => $type->id()]);
    }

    $build['#content'] = $content;

    return $build;
  }

  /**
   * Provides the event submission form.
   *
   * @param \Drupal\event\EventTypeInterface $event_type
   *   The event type entity for the event.
   *
   * @return array
   *   A event submission form.
   */
  public function add(EventTypeInterface $event_type) {
    $event = $this->entityManager()->getStorage('event')->create([
      'type' => $event_type->id(),
    ]);

    $form = $this->entityFormBuilder()->getForm($event);

    return $form;
  }

  /**
   * Displays a event revision.
   *
   * @param int $event_revision
   *   The event revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($event_revision) {
    $event = $this->entityManager()->getStorage('event')->loadRevision($event_revision);
    $event = $this->entityManager()->getTranslationFromContext($event);
    $event_view_controller = new EventViewController($this->entityManager, $this->renderer, $this->currentUser());
    $page = $event_view_controller->view($event);
    unset($page['events'][$event->id()]['#cache']);
    return $page;
  }

  /**
   * Page title callback for a event revision.
   *
   * @param int $event_revision
   *   The event revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($event_revision) {
    $event = $this->entityManager()->getStorage('event')->loadRevision($event_revision);
    return $this->t('Revision of %title from %date', ['%title' => $event->label(), '%date' => format_date($event->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a event.
   *
   * @param \Drupal\event\EventInterface $event
   *   A event object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(EventInterface $event) {
    $account = $this->currentUser();
    $langcode = $event->language()->getId();
    $langname = $event->language()->getName();
    $languages = $event->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $event_storage = $this->entityManager()->getStorage('event');
    $type = $event->getType();

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $event->label()]) : $this->t('Revisions for %title', ['%title' => $event->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert $type revisions") || $account->hasPermission('revert all revisions') || $account->hasPermission('administer events')) && $event->access('update'));
    $delete_permission = (($account->hasPermission("delete $type revisions") || $account->hasPermission('delete all revisions') || $account->hasPermission('administer events')) && $event->access('delete'));

    $rows = [];
    $default_revision = $event->getRevisionId();
    $current_revision_displayed = FALSE;

    foreach ($this->getRevisionIds($event, $event_storage) as $vid) {
      /** @var \Drupal\event\EventInterface $revision */
      $revision = $event_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->revision_timestamp->value, 'short');

        // We treat also the latest translation-affecting revision as current
        // revision, if it was the default revision, as its values for the
        // current language will be the same of the current default revision in
        // this case.
        $is_current_revision = $vid == $default_revision || (!$current_revision_displayed && $revision->wasDefaultRevision());
        if (!$is_current_revision) {
          $link = $this->l($date, new Url('entity.event.revision', ['event' => $event->id(), 'event_revision' => $vid]));
        }
        else {
          $link = $event->link($date);
          $current_revision_displayed = TRUE;
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => ['#markup' => $revision->revision_log->value, '#allowed_tags' => Xss::getHtmlTagList()],
            ],
          ],
        ];
        // @todo Simplify once https://www.drupal.org/event/2334319 lands.
        $this->renderer->addCacheableDependency($column['data'], $username);
        $row[] = $column;

        if ($is_current_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];

          $rows[] = [
            'data' => $row,
            'class' => ['revision-current'],
          ];
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $vid < $event->getRevisionId() ? $this->t('Revert') : $this->t('Set as current revision'),
              'url' => $has_translations ?
                Url::fromRoute('event.revision_revert_translation_confirm', ['event' => $event->id(), 'event_revision' => $vid, 'langcode' => $langcode]) :
                Url::fromRoute('event.revision_revert_confirm', ['event' => $event->id(), 'event_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('event.revision_delete_confirm', ['event' => $event->id(), 'event_revision' => $vid]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];

          $rows[] = $row;
        }
      }
    }

    $build['event_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
      '#attached' => [
        'library' => ['event/drupal.event.admin'],
      ],
      '#attributes' => ['class' => 'event-revision-table'],
    ];

    $build['pager'] = ['#type' => 'pager'];

    return $build;
  }

  /**
   * The _title_callback for the event.add route.
   *
   * @param \Drupal\event\EventTypeInterface $event_type
   *   The current event.
   *
   * @return string
   *   The page title.
   */
  public function addPageTitle(EventTypeInterface $event_type) {
    return $this->t('Create @name event', ['@name' => $event_type->label()]);
  }

  /**
   * Gets a list of event revision IDs for a specific event.
   *
   * @param \Drupal\event\EventInterface $event
   *   The event entity.
   * @param \Drupal\event\EventStorageInterface $event_storage
   *   The event storage handler.
   *
   * @return int[]
   *   Event revision IDs (in descending order).
   */
  protected function getRevisionIds(EventInterface $event, EventStorageInterface $event_storage) {
    $result = $event_storage->getQuery()
      ->allRevisions()
      ->condition($event->getEntityType()->getKey('id'), $event->id())
      ->sort($event->getEntityType()->getKey('revision'), 'DESC')
      ->pager(50)
      ->execute();
    return array_keys($result);
  }

}
