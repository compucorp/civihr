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
    },
    events: {
      "change [name=activity_type_id]": function(e) {
        var contact_id = CRM.absenceApp.contactId;
        var newActivityId = $(e.currentTarget).val();
        var addActivityUrl = CRM.url("civicrm/absence/set", {'reset':1, 'action': 'add', 'atype': newActivityId, 'cid': contact_id});
        window.location = addActivityUrl;
      }
    },
    onRender: function() {
      this.$('[name=activity_type_id]').multiselect({
        minWidth: 250,
        multiple: false,
        header: false,
        noneSelectedText: ts('(Select type)'),
        selectedText: ts('(Select type)')
      });
    }
  });
});
