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
      'change:is_paid': 'togglePayGrade'
    }),
    events: _.extend({}, HRApp.Common.Views.StandardForm.prototype.events,{
      'change [name=pay_is_auto_est]': 'toggleLockMessage',
    }),
    onRender: function() {
      HRApp.Common.Views.StandardForm.prototype.onRender.apply(this, arguments);
      if (this.model.get('is_paid') == 1) {
        this.$('.hrjob-needs-pay_grade').show();
      } else {
        this.$('.hrjob-needs-pay_grade').hide();
      }
      this.$('.pay_annualized_est_expl').text(ts('(Estimates will be updated based on pay-rate, FTE, and system settings.)'));
      // FIXME: figure out which field in this.options.settings is applicable and output it
      this.$('[name=pay_is_auto_est]').lockButton({
        lockedText: ts('Automatic estimate'),
        unlockedText: ts('Manual estimate'),
        for: this.$('[name=pay_annualized_est]'),
        value: this.model.get('pay_annualized_est') // FIXME use callback to calculate automatically
      });
      this.toggleLockMessage();

      //set the default currency on Pay screen
      var $currency = this.$("select#hrjob-pay_currency").val();
      if ($currency == "") {
        this.$("select#hrjob-pay_currency").val(CRM.jobTabApp.defaultCurrency);
        $("#s2id_hrjob-pay_currency .select2-choice span").first().text(CRM.jobTabApp.defaultCurrency);
      }
    },
    events: {
      'click .standard-save': 'doSave',
      'click .standard-reset': 'doReset',
      'click .pay_annualized_est_edit': 'doAnnualizedEstEdit'
    },
    doSave: function(){
      var view = this,
        $hrs_unit = $("#s2id_hrjob-pay_currency .select2-choice span").first().text(),
        $hrs_amt = $("#hrjob-pay_currency").val();
      for (k in this.model.attributes) {
        if (k === 'pay_currency') {
          this.model.attributes[k] = $hrs_amt;
        }
      }
      this.model.save({}, {
        success: function() {
          HRApp.trigger('ui:unblock');
          CRM.alert(ts('Saved'), null, 'success');
          view.modelBackup = view.model.toJSON();
          view.render();
          view.triggerMethod('standard:save', view, view.model);
        },
        error: function() {
          HRApp.trigger('ui:block', ts('Error while saving. Please reload and retry.'));
        }
      });
      return false;
    },
    toggleLockMessage: function() {
      var locked = this.$('[name=pay_is_auto_est]').lockButton('isLocked');
      this.$('.pay_annualized_est_expl').parent().css('visibility', locked ? 'visible' : 'hidden');
    },
    togglePayGrade: function() {
      var view = this;
      if (this.model.get('is_paid') == 1) {
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
      e.stopPropagation();
      var view = new Pay.EditSettings({
        model: this.options.settings
      });
      view.on('standard:save', function(){
        HRApp.dialogRegion.close();
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
    },
    onClose: function() {
      $('.hrjob-dialog-region').dialog('close');
    }
  });
});
