CRM.HRApp.module('JobTabApp.General', function(General, HRApp, Backbone, Marionette, $, _) {
  General.SummaryView = Marionette.ItemView.extend({
    template: '#hrjob-general-summary-template',
    templateHelpers: function() {
      return {
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJob
      };
    },
    modelEvents: {
      'change:manager_contact_id': 'renderManagerContact'
    },
    initialize: function() {
      CRM.HRApp.Common.mbind(this);
    },
    onRender: function() {
      this.renderManagerContact();
    },
    renderManagerContact: function() {
      this.$('a.hrjob-manager_contact').hrContactLink({
        cid: this.model.get('manager_contact_id')
      });
    }
  });

});