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
      'change:funding_org_id': 'renderFundingOrg'
    },
    initialize: function() {
      CRM.HRApp.Common.mbind(this);
    },
    onRender: function() {
      this.renderFundingOrg();
    },
    renderFundingOrg: function() {
      this.$('a.hrjob-funding_org_id').hrContactLink({
        cid: this.model.get('funding_org_id')
      });
    }
  });

});