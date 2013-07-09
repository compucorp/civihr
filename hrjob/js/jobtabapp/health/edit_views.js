CRM.HRApp.module('JobTabApp.Health', function(Health, HRApp, Backbone, Marionette, $, _){
  Health.EditView = Marionette.ItemView.extend({
    template: '#hrjob-health-template'
  });
});
