CRM.HRApp.module('JobTabApp.Leave', function(Leave, HRApp, Backbone, Marionette, $, _){
  Leave.EditView = Marionette.ItemView.extend({
    template: '#hrjob-leave-template'
  });
});
