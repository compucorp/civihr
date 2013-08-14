CRM.HRApp.module('JobTabApp.Pay', function(Pay, HRApp, Backbone, Marionette, $, _) {
  Pay.SummaryView = Marionette.ItemView.extend({
    template: '#hrjob-pay-summary-template',
    templateHelpers: function() {
      return {
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJobPay
      };
    },
    initialize: function() {
      CRM.HRApp.Common.mbind(this);
    }
  });
});