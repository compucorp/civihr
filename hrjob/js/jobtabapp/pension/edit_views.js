CRM.HRApp.module('JobTabApp.Pension', function(Pension, HRApp, Backbone, Marionette, $, _){
  Pension.EditView = Marionette.ItemView.extend({
    template: '#hrjob-pension-template',
    templateHelpers: function() {
      return {
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJobPension
      };
    }
  });
});
