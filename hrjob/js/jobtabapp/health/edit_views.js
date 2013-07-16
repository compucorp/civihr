CRM.HRApp.module('JobTabApp.Health', function(Health, HRApp, Backbone, Marionette, $, _){
  Health.EditView = Marionette.ItemView.extend({
    template: '#hrjob-health-template',
    templateHelpers: function() {
      return {
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJobHealth
      };
    },
    initialize: function() {
      CRM.HRApp.Common.mbind(this);
    }
  });
});
