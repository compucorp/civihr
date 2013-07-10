CRM.HRApp.module('JobTabApp.Pay', function(Pay, HRApp, Backbone, Marionette, $, _){
  Pay.EditView = Marionette.ItemView.extend({
    template: '#hrjob-pay-template'
  });
});
