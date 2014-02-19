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
    events: _.extend({}, HRApp.Common.Views.StandardForm.prototype.events,{
      'click .pay_annualized_est_edit': 'doAnnualizedEstEdit'
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
    doAnnualizedEstEdit: function(e) {
      e.preventDefault();
      e.stopPropagation();
      var view = new Pay.EditSettings({
        model: new HRApp.Entities.Setting()
      });
      HRApp.dialogRegion.show(view);
    },
    onValidateRulesCreate: function(view, r) {
      _.extend(r.rules, {
        pay_amount: {
          number: true
        }
      });
    }
  });
  Pay.EditSettings = HRApp.Common.Views.StandardForm.extend({
    template: '#hrjob-pay-settings-template',
    templateHelpers: function() {
          return {
            'isNew': this.model.get('id') ? false : true,
            'RenderUtil': CRM.HRApp.RenderUtil,
            'FieldOptions': CRM.FieldOptions.HRJobPay
          };
    },
    onShow: function() {
      $('.hrjob-dialog-region').dialog({
        modal: true,
        title: "Anualized Pay Constants",
        width: "auto"
      });
    }
  });
});
