<?php

namespace Drupal\event;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the event entity type.
 */
class EventViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['event_field_data']['table']['base']['weight'] = -10;
    $data['event_field_data']['table']['base']['access query tag'] = 'event_access';
    $data['event_field_data']['table']['wizard_id'] = 'event';

    $data['event_field_data']['eid']['argument'] = [
      'id' => 'event_eid',
      'name field' => 'title',
      'numeric' => TRUE,
      'validate type' => 'eid',
    ];

    $data['event_field_data']['title']['field']['default_formatter_settings'] = ['link_to_entity' => TRUE];

    $data['event_field_data']['title']['field']['link_to_event default'] = TRUE;

    $data['event_field_data']['type']['argument']['id'] = 'event_type';

    $data['event_field_data']['langcode']['help'] = $this->t('The language of the content or translation.');

    $data['event_field_data']['status']['filter']['label'] = $this->t('Published status');
    $data['event_field_data']['status']['filter']['type'] = 'yes-no';
    // Use status = 1 instead of status <> 0 in WHERE statement.
    $data['event_field_data']['status']['filter']['use_equal'] = TRUE;

    $data['event_field_data']['status_extra'] = [
      'title' => $this->t('Published status or admin user'),
      'help' => $this->t('Filters out unpublished content if the current user cannot view it.'),
      'filter' => [
        'field' => 'status',
        'id' => 'event_status',
        'label' => $this->t('Published status or admin user'),
      ],
    ];

    $data['event_field_data']['promote']['help'] = $this->t('A boolean indicating whether the event is visible on the front page.');
    $data['event_field_data']['promote']['filter']['label'] = $this->t('Promoted to front page status');
    $data['event_field_data']['promote']['filter']['type'] = 'yes-no';

    $data['event_field_data']['sticky']['help'] = $this->t('A boolean indicating whether the event should sort to the top of content lists.');
    $data['event_field_data']['sticky']['filter']['label'] = $this->t('Sticky status');
    $data['event_field_data']['sticky']['filter']['type'] = 'yes-no';
    $data['event_field_data']['sticky']['sort']['help'] = $this->t('Whether or not the content is sticky. To list sticky content first, set this to descending.');

    $data['event']['event_bulk_form'] = [
      'title' => $this->t('Event operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple events.'),
      'field' => [
        'id' => 'event_bulk_form',
      ],
    ];

    // Bogus fields for aliasing purposes.

    // @todo Add similar support to any date field
    // @see https://www.drupal.org/event/2337507
    $data['event_field_data']['created_fulldate'] = [
      'title' => $this->t('Created date'),
      'help' => $this->t('Date in the form of CCYYMMDD.'),
      'argument' => [
        'field' => 'created',
        'id' => 'date_fulldate',
      ],
    ];

    $data['event_field_data']['created_year_month'] = [
      'title' => $this->t('Created year + month'),
      'help' => $this->t('Date in the form of YYYYMM.'),
      'argument' => [
        'field' => 'created',
        'id' => 'date_year_month',
      ],
    ];

    $data['event_field_data']['created_year'] = [
      'title' => $this->t('Created year'),
      'help' => $this->t('Date in the form of YYYY.'),
      'argument' => [
        'field' => 'created',
        'id' => 'date_year',
      ],
    ];

    $data['event_field_data']['created_month'] = [
      'title' => $this->t('Created month'),
      'help' => $this->t('Date in the form of MM (01 - 12).'),
      'argument' => [
        'field' => 'created',
        'id' => 'date_month',
      ],
    ];

    $data['event_field_data']['created_day'] = [
      'title' => $this->t('Created day'),
      'help' => $this->t('Date in the form of DD (01 - 31).'),
      'argument' => [
        'field' => 'created',
        'id' => 'date_day',
      ],
    ];

    $data['event_field_data']['created_week'] = [
      'title' => $this->t('Created week'),
      'help' => $this->t('Date in the form of WW (01 - 53).'),
      'argument' => [
        'field' => 'created',
        'id' => 'date_week',
      ],
    ];

    $data['event_field_data']['changed_fulldate'] = [
      'title' => $this->t('Updated date'),
      'help' => $this->t('Date in the form of CCYYMMDD.'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_fulldate',
      ],
    ];

    $data['event_field_data']['changed_year_month'] = [
      'title' => $this->t('Updated year + month'),
      'help' => $this->t('Date in the form of YYYYMM.'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_year_month',
      ],
    ];

    $data['event_field_data']['changed_year'] = [
      'title' => $this->t('Updated year'),
      'help' => $this->t('Date in the form of YYYY.'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_year',
      ],
    ];

    $data['event_field_data']['changed_month'] = [
      'title' => $this->t('Updated month'),
      'help' => $this->t('Date in the form of MM (01 - 12).'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_month',
      ],
    ];

    $data['event_field_data']['changed_day'] = [
      'title' => $this->t('Updated day'),
      'help' => $this->t('Date in the form of DD (01 - 31).'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_day',
      ],
    ];

    $data['event_field_data']['changed_week'] = [
      'title' => $this->t('Updated week'),
      'help' => $this->t('Date in the form of WW (01 - 53).'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_week',
      ],
    ];

    $data['event_field_data']['uid']['help'] = $this->t('The user authoring the content. If you need more fields than the uid add the content: author relationship');
    $data['event_field_data']['uid']['filter']['id'] = 'user_name';
    $data['event_field_data']['uid']['relationship']['title'] = $this->t('Content author');
    $data['event_field_data']['uid']['relationship']['help'] = $this->t('Relate content to the user who created it.');
    $data['event_field_data']['uid']['relationship']['label'] = $this->t('author');

    $data['event']['event_listing_empty'] = [
      'title' => $this->t('Empty Event Frontpage behavior'),
      'help' => $this->t('Provides a link to the event add overview page.'),
      'area' => [
        'id' => 'event_listing_empty',
      ],
    ];

    $data['event_field_data']['uid_revision']['title'] = $this->t('User has a revision');
    $data['event_field_data']['uid_revision']['help'] = $this->t('All events where a certain user has a revision');
    $data['event_field_data']['uid_revision']['real field'] = 'eid';
    $data['event_field_data']['uid_revision']['filter']['id'] = 'event_uid_revision';
    $data['event_field_data']['uid_revision']['argument']['id'] = 'event_uid_revision';

    $data['event_field_revision']['table']['wizard_id'] = 'event_revision';

    // Advertise this table as a possible base table.
    $data['event_field_revision']['table']['base']['help'] = $this->t('Content revision is a history of changes to content.');
    $data['event_field_revision']['table']['base']['defaults']['title'] = 'title';

    $data['event_field_revision']['eid']['argument'] = [
      'id' => 'event_eid',
      'numeric' => TRUE,
    ];
    // @todo the EID field needs different behaviour on revision/non-revision
    //   tables. It would be neat if this could be encoded in the base field
    //   definition.
    $data['event_field_revision']['eid']['relationship']['id'] = 'standard';
    $data['event_field_revision']['eid']['relationship']['base'] = 'event_field_data';
    $data['event_field_revision']['eid']['relationship']['base field'] = 'eid';
    $data['event_field_revision']['eid']['relationship']['title'] = $this->t('Content');
    $data['event_field_revision']['eid']['relationship']['label'] = $this->t('Get the actual content from a content revision.');

    $data['event_field_revision']['vid'] = [
      'argument' => [
        'id' => 'event_vid',
        'numeric' => TRUE,
      ],
      'relationship' => [
        'id' => 'standard',
        'base' => 'event_field_data',
        'base field' => 'vid',
        'title' => $this->t('Content'),
        'label' => $this->t('Get the actual content from a content revision.'),
      ],
    ] + $data['event_field_revision']['vid'];

    $data['event_field_revision']['langcode']['help'] = $this->t('The language the original content is in.');

    $data['event_revision']['revision_uid']['help'] = $this->t('Relate a content revision to the user who created the revision.');
    $data['event_revision']['revision_uid']['relationship']['label'] = $this->t('revision user');

    $data['event_field_revision']['table']['wizard_id'] = 'event_field_revision';

    $data['event_field_revision']['table']['join']['event_field_data']['left_field'] = 'vid';
    $data['event_field_revision']['table']['join']['event_field_data']['field'] = 'vid';

    $data['event_field_revision']['status']['filter']['label'] = $this->t('Published');
    $data['event_field_revision']['status']['filter']['type'] = 'yes-no';
    $data['event_field_revision']['status']['filter']['use_equal'] = TRUE;

    $data['event_field_revision']['promote']['help'] = $this->t('A boolean indicating whether the event is visible on the front page.');

    $data['event_field_revision']['sticky']['help'] = $this->t('A boolean indicating whether the event should sort to the top of content lists.');

    $data['event_field_revision']['langcode']['help'] = $this->t('The language of the content or translation.');

    $data['event_field_revision']['link_to_revision'] = [
      'field' => [
        'title' => $this->t('Link to revision'),
        'help' => $this->t('Provide a simple link to the revision.'),
        'id' => 'event_revision_link',
        'click sortable' => FALSE,
      ],
    ];

    $data['event_field_revision']['revert_revision'] = [
      'field' => [
        'title' => $this->t('Link to revert revision'),
        'help' => $this->t('Provide a simple link to revert to the revision.'),
        'id' => 'event_revision_link_revert',
        'click sortable' => FALSE,
      ],
    ];

    $data['event_field_revision']['delete_revision'] = [
      'field' => [
        'title' => $this->t('Link to delete revision'),
        'help' => $this->t('Provide a simple link to delete the content revision.'),
        'id' => 'event_revision_link_delete',
        'click sortable' => FALSE,
      ],
    ];

    // Define the base group of this table. Fields that don't have a group defined
    // will go into this field by default.
    $data['event_access']['table']['group']  = $this->t('Content access');

    // For other base tables, explain how we join.
    $data['event_access']['table']['join'] = [
      'event_field_data' => [
        'left_field' => 'eid',
        'field' => 'eid',
      ],
    ];
    $data['event_access']['eid'] = [
      'title' => $this->t('Access'),
      'help' => $this->t('Filter by access.'),
      'filter' => [
        'id' => 'event_access',
        'help' => $this->t('Filter for content by view access. <strong>Not necessary if you are using event as your base table.</strong>'),
      ],
    ];

    // Add search table, fields, filters, etc., but only if a page using the
    // event_search plugin is enabled.
    if (\Drupal::moduleHandler()->moduleExists('search')) {
      $enabled = FALSE;
      $search_page_repository = \Drupal::service('search.search_page_repository');
      foreach ($search_page_repository->getActiveSearchpages() as $page) {
        if ($page->getPlugin()->getPluginId() == 'event_search') {
          $enabled = TRUE;
          break;
        }
      }

      if ($enabled) {
        $data['event_search_index']['table']['group'] = $this->t('Search');

        // Automatically join to the event table (or actually, event_field_data).
        // Use a Views table alias to allow other modules to use this table too,
        // if they use the search index.
        $data['event_search_index']['table']['join'] = [
          'event_field_data' => [
            'left_field' => 'eid',
            'field' => 'sid',
            'table' => 'search_index',
            'extra' => "event_search_index.type = 'event_search' AND event_search_index.langcode = event_field_data.langcode",
          ]
        ];

        $data['event_search_total']['table']['join'] = [
          'event_search_index' => [
            'left_field' => 'word',
            'field' => 'word',
          ],
        ];

        $data['event_search_dataset']['table']['join'] = [
          'event_field_data' => [
            'left_field' => 'sid',
            'left_table' => 'event_search_index',
            'field' => 'sid',
            'table' => 'search_dataset',
            'extra' => 'event_search_index.type = event_search_dataset.type AND event_search_index.langcode = event_search_dataset.langcode',
            'type' => 'INNER',
          ],
        ];

        $data['event_search_index']['score'] = [
          'title' => $this->t('Score'),
          'help' => $this->t('The score of the search item. This will not be used if the search filter is not also present.'),
          'field' => [
            'id' => 'search_score',
            'float' => TRUE,
            'no group by' => TRUE,
          ],
          'sort' => [
            'id' => 'search_score',
            'no group by' => TRUE,
          ],
        ];

        $data['event_search_index']['keys'] = [
          'title' => $this->t('Search Keywords'),
          'help' => $this->t('The keywords to search for.'),
          'filter' => [
            'id' => 'search_keywords',
            'no group by' => TRUE,
            'search_type' => 'event_search',
          ],
          'argument' => [
            'id' => 'search',
            'no group by' => TRUE,
            'search_type' => 'event_search',
          ],
        ];

      }
    }

    return $data;
  }

}
