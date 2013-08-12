CRM.HRApp.module('JobTabApp.Pension', function(Pension, HRApp, Backbone, Marionette, $, _){
  Pension.EditView = HRApp.Common.Views.StandardForm.extend({
    template: '#hrjob-pension-template',
    templateHelpers: function() {
      return {
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJobPension
      };
    },
    onValidateRulesCreate: function(view, r) {
      _.extend(r.rules, {
        contrib_pct: {
          number: true,
          range: [0, 100]
        }
      });
    }
  });
});
