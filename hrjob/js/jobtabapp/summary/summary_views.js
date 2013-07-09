CRM.HRApp.module('JobTabApp.Summary', function(Summary, HRApp, Backbone, Marionette, $, _){
  Summary.ShowView = Marionette.ItemView.extend({
    template: '#hrjob-summary-template'
  });
});
