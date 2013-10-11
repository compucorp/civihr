// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRApp.module('JobTabApp.Funding', function(Funding, HRApp, Backbone, Marionette, $, _) {
	Funding.SummaryView = Marionette.ItemView.extend({
    template: '#hrjob-funding-summary-template',
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