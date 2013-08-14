CRM.HRApp.module('JobTabApp.Health', function(Health, HRApp, Backbone, Marionette, $, _) {
  Health.SummaryView = Marionette.ItemView.extend({
    template: '#hrjob-health-summary-template',
    templateHelpers: function() {
      return {
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJobHealth
      };
    },
    initialize: function() {
      CRM.HRApp.Common.mbind(this);
    }
  });
});