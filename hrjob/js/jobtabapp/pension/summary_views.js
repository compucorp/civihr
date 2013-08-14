CRM.HRApp.module('JobTabApp.Pension', function(Pension, HRApp, Backbone, Marionette, $, _) {
  Pension.SummaryView = Marionette.ItemView.extend({
    template: '#hrjob-pension-summary-template',
    templateHelpers: function() {
      return {
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJobPension
      };
    },
    initialize: function() {
      CRM.HRApp.Common.mbind(this);
    }
  });
});