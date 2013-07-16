CRM.HRApp.module('JobTabApp.Hour', function(Hour, HRApp, Backbone, Marionette, $, _){
  Hour.EditView = Marionette.ItemView.extend({
    template: '#hrjob-hour-template',
    templateHelpers: function() {
      return {
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJobHour
      };
    },
    initialize: function() {
      CRM.HRApp.Common.mbind(this);
    }
  });
});
