CRM.HRApp.module('JobTabApp.Role', function(Role, HRApp, Backbone, Marionette, $, _){
  Role.EditView = Marionette.ItemView.extend({
    template: '#hrjob-role-template'
  });
});
