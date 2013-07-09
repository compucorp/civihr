CRM.HRApp.module('JobTabApp.Hour', function(Hour, HRApp, Backbone, Marionette, $, _){
  Hour.EditView = Marionette.ItemView.extend({
    template: '#hrjob-hour-template'
  });
});
