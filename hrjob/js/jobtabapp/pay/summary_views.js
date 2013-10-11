// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRApp.module('JobTabApp.Pay', function(Pay, HRApp, Backbone, Marionette, $, _) {
  Pay.ShowView = Marionette.ItemView.extend({
    template: '#hrjob-pay-summary-template',
    templateHelpers: function() {
      return {
         'RenderUtil': CRM.HRApp.RenderUtil,
         'FieldOptions': CRM.FieldOptions['HRJobPay']
      };
    },
    initialize: function() {
      CRM.HRApp.Common.mbind(this);
    },
    onBindingCreate: function(bindings) {
      if (this.$('span[name=pay_amount]').length > 1) {
        bindings.pay_amount = {
          selector: 'span[name=pay_amount]',
          converter: HRApp.Common.formatCurrency
        };
      }
    }
  });
});