CRM.HRApp.module('JobTabApp.Pay', function(Pay, HRApp, Backbone, Marionette, $, _){
  Pay.EditView = HRApp.Common.Views.StandardForm.extend({
    template: '#hrjob-pay-template',
    templateHelpers: function() {
      return {
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJobPay
      };
    }
  });
});
