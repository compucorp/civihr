// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRAbsenceApp.module('Filter', function(Filter, HRAbsenceApp, Backbone, Marionette, $, _) {
  Filter.FilterView = Marionette.ItemView.extend({
    template: '#hrabsence-filters-template',
    templateHelpers: function() {
      return {
        // 'RenderUtil': CRM.HRAbsenceApp.RenderUtil,
        'FieldOptions': {
          'activity_type_id': CRM.absenceApp.activityTypes,
          'period_id': CRM.absenceApp.periods
        }
      };
    }
    // FIXME: bind model properties to HTML widgets
  });
});