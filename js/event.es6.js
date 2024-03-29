/**
 * @file
 * Defines Javascript behaviors for the event module.
 */

(function ($, Drupal, drupalSettings) {
  /**
   * Behaviors for tabs in the event edit form.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches summary behavior for tabs in the event edit form.
   */
  Drupal.behaviors.eventDetailsSummaries = {
    attach(context) {
      const $context = $(context);

      $context.find('.event-form-author').drupalSetSummary((context) => {
        const $authorContext = $(context);
        const name = $authorContext.find('.field--name-uid input').val();
        const date = $authorContext.find('.field--name-created input').val();

        if (name && date) {
          return Drupal.t('By @name on @date', { '@name': name, '@date': date });
        }
        else if (name) {
          return Drupal.t('By @name', { '@name': name });
        }
        else if (date) {
          return Drupal.t('Authored on @date', { '@date': date });
        }
      });

      $context.find('.event-form-options').drupalSetSummary((context) => {
        const $optionsContext = $(context);
        const vals = [];

        if ($optionsContext.find('input').is(':checked')) {
          $optionsContext.find('input:checked').next('label').each(function () {
            vals.push(Drupal.checkPlain($.trim($(this).text())));
          });
          return vals.join(', ');
        }

        return Drupal.t('Not promoted');
      });
    },
  };
}(jQuery, Drupal, drupalSettings));
