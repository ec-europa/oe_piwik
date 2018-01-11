/**
 * @file
 * This file provides a summary to the tabs of the configuration form.
 */

(function ($, window) {

  "use strict";

  /**
   * Provide the summary information for the tracking settings vertical tabs.
   */
  Drupal.behaviors.trackingSettingsSummary = {
    attach: function (context) {
      // Make sure this behavior is processed only if drupalSetSummary is defined.
      if (typeof jQuery.fn.drupalSetSummary === 'undefined') {
        return;
      }

      $('#edit-page-vis-settings').drupalSetSummary(function (context) {
        var $radio = $('input[name="oe_piwik_visibility_pages"]:checked', context);
        if ($radio.val() === '0') {
          if (!$('textarea[name="oe_piwik_pages"]', context).val()) {
            return Drupal.t('Not restricted');
          }
          else {
            return Drupal.t('All pages with exceptions');
          }
        }
        else {
          return Drupal.t('Restricted to certain pages');
        }
      });
      $('#edit-role-vis-settings').drupalSetSummary(function (context) {
        var vals = [];
        $('input[type="checkbox"]:checked', context).each(function () {
          vals.push($.trim($(this).next('label').text()));
        });
        if (!vals.length) {
          return Drupal.t('Not restricted');
        }
        else if ($('input[name="oe_piwik_visibility_roles"]:checked', context).val() === '1') {
          return Drupal.t('Excepted: @roles', {'@roles': vals.join(', ')});
        }
        else {
          return vals.join(', ');
        }
      });
    }
  };
})(jQuery);
