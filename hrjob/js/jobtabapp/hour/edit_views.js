CRM.HRApp.module('JobTabApp.Hour', function(Hour, HRApp, Backbone, Marionette, $, _){
  Hour.EditView = HRApp.Common.Views.StandardForm.extend({
    template: '#hrjob-hour-template',
    templateHelpers: function() {
      return {
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJobHour
      };
    }
  });
});
