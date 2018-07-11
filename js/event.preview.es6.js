/**
 * @file
 * Preview behaviors.
 */

(function ($, Drupal) {
  /**
   * Disables all non-relevant links in event previews.
   *
   * Destroys links (except local fragment identifiers such as href="#frag") in
   * event previews to prevent users from leaving the page.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches confirmation prompt for clicking links in event preview mode.
   * @prop {Drupal~behaviorDetach} detach
   *   Detaches confirmation prompt for clicking links in event preview mode.
   */
  Drupal.behaviors.eventPreviewDestroyLinks = {
    attach(context) {
      function clickPreviewModal(event) {
        // Only confirm leaving previews when left-clicking and user is not
        // pressing the ALT, CTRL, META (Command key on the Macintosh keyboard)
        // or SHIFT key.
        if (event.button === 0 && !event.altKey && !event.ctrlKey && !event.metaKey && !event.shiftKey) {
          event.preventDefault();
          const $previewDialog = $(`<div>${Drupal.theme('eventPreviewModal')}</div>`).appendTo('body');
          Drupal.dialog($previewDialog, {
            title: Drupal.t('Leave preview?'),
            buttons: [
              {
                text: Drupal.t('Cancel'),
                click() {
                  $(this).dialog('close');
                },
              }, {
                text: Drupal.t('Leave preview'),
                click() {
                  window.top.location.href = event.target.href;
                },
              },
            ],
          }).showModal();
        }
      }

      const $preview = $(context).find('.content').once('event-preview');
      if ($(context).find('.event-preview-container').length) {
        $preview.on('click.preview', 'a:not([href^=#], #edit-backlink, #toolbar-administration a)', clickPreviewModal);
      }
    },
    detach(context, settings, trigger) {
      if (trigger === 'unload') {
        const $preview = $(context).find('.content').removeOnce('event-preview');
        if ($preview.length) {
          $preview.off('click.preview');
        }
      }
    },
  };

  /**
   * Switch view mode.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches automatic submit on `formUpdated.preview` events.
   */
  Drupal.behaviors.eventPreviewSwitchViewMode = {
    attach(context) {
      const $autosubmit = $(context).find('[data-drupal-autosubmit]').once('autosubmit');
      if ($autosubmit.length) {
        $autosubmit.on('formUpdated.preview', function () {
          $(this.form).trigger('submit');
        });
      }
    },
  };

  /**
   * Theme function for event preview modal.
   *
   * @return {string}
   *   Markup for the event preview modal.
   */
  Drupal.theme.eventPreviewModal = function () {
    return `<p>${
      Drupal.t('Leaving the preview will cause unsaved changes to be lost. Are you sure you want to leave the preview?')
      }</p><small class="description">${
      Drupal.t('CTRL+Left click will prevent this dialog from showing and proceed to the clicked link.')}</small>`;
  };
}(jQuery, Drupal));
