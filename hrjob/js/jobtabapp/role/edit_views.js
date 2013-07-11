CRM.HRApp.module('JobTabApp.Role', function(Role, HRApp, Backbone, Marionette, $, _){
  Role.EditView = Marionette.ItemView.extend({
    template: '#hrjob-role-template',
    templateHelpers: function() {
      return {
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJobRole
      };
    }
  });
});
