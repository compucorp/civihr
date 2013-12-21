// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRAbsenceApp.module('New', function(New, HRAbsenceApp, Backbone, Marionette, $, _) {
  New.NewView = Marionette.ItemView.extend({
    template: '#hrabsence-new-template',
    templateHelpers: function() {
      return {
        // 'RenderUtil': CRM.HRAbsenceApp.RenderUtil,
        'FieldOptions': {
          'activity_type_id': CRM.absenceApp.activityTypes
        }
      };
    }
    // FIXME: bind model properties to HTML widgets
  });
});