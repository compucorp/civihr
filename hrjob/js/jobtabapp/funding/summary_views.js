// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRApp.module('JobTabApp.Funding', function(Funding, HRApp, Backbone, Marionette, $, _) {
  Funding.SummaryView = Marionette.ItemView.extend({
    template: '#hrjob-funding-summary-template',
    templateHelpers: function() {
      return {
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJob
      };
    }
  });
});