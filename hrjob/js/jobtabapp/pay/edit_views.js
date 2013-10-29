// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRApp.module('JobTabApp.Pay', function(Pay, HRApp, Backbone, Marionette, $, _){
  Pay.EditView = HRApp.Common.Views.StandardForm.extend({
    template: '#hrjob-pay-template',
    templateHelpers: function() {
      return {
        'isNew': this.model.get('id') ? false : true,
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJobPay
      };
    },
    modelEvents: _.extend({}, HRApp.Common.Views.StandardForm.prototype.modelEvents, {
      'change:pay_grade': 'togglePayGrade'
    }),
    onRender: function() {
      HRApp.Common.Views.StandardForm.prototype.onRender.apply(this, arguments);
      if (this.model.get('pay_grade') == 'paid') {
        this.$('.hrjob-needs-pay_grade').show();
      } else {
        this.$('.hrjob-needs-pay_grade').hide();
      }
    },
    togglePayGrade: function() {
      var view = this;
      if (this.model.get('pay_grade') == 'paid') {
        view.$('.hrjob-needs-pay_grade:hidden').slideDown({
          complete: function() {
            view.$('[name=pay_currency]').focus();
          }
        });
      } else {
        view.$('.hrjob-needs-pay_grade').slideUp();
      }
    },
    onValidateRulesCreate: function(view, r) {
      _.extend(r.rules, {
        pay_amount: {
          number: true
        }
      });
    }
  });
});
