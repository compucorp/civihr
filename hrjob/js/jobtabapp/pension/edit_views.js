// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRApp.module('JobTabApp.Pension', function(Pension, HRApp, Backbone, Marionette, $, _){
  Pension.EditView = HRApp.Common.Views.StandardForm.extend({
    template: '#hrjob-pension-template',
    templateHelpers: function() {
      return {
        'isNew': this.model.get('id') ? false : true,
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJobPension
      };
    },
    onValidateRulesCreate: function(view, r) {
      _.extend(r.rules, {
        er_contrib_pct: {
          number: true,
          range: [0, 100]
        },
        ee_contrib_pct: {
          number: true,
          range: [0, 100]
        },
        ee_contrib_abs: {
		  number: true,
        } 
      });
    }
  });
});
