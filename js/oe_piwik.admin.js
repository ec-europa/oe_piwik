/**
 * @file
 * This file provides a summary to the tabs of the configuration form.
 */

(function ($) {

  /**
   * Provide the summary information for the tracking settings vertical tabs.
   */
  Drupal.behaviors.trackingSettingsSummary = {
    attach: function (context) {
      if ($('fieldset#edit-page-vis-settings').length > 0) {
        if ($('input[name="oe_piwik_visibility_pages"]:checked').val() == 0) {
          if ($('textarea[name="oe_piwik_pages"]').val() == '') {
            return Drupal.t('Not restricted');
          }
          else {
            return Drupal.t('All pages with exceptions');
          }
        }
        else {
          return Drupal.t('Restricted to certain pages');
        }
      }
      if ($('fieldset#edit-role-vis-settings').length > 0) {
        var vals = [];
        $('input[type="checkbox"]:checked').each(function () {
          vals.push($.trim($(this).next('label').text()));
        });
        if (vals.length < 1) {
          return Drupal.t('Not restricted');
        }
        else if ($('input[name="oe_piwik_visibility_roles"]:checked').val() == 1) {
          return Drupal.t('Excepted: @roles', {'@roles' : vals.join(', ')});
        }
        else {
          return vals.join(', ');
        }
      }
    }
  };
})(jQuery);
