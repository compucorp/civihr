CRM.HRApp.module('JobTabApp.General', function(General, HRApp, Backbone, Marionette, $, _) {
  General.EditView = HRApp.Common.Views.StandardForm.extend({
    template: '#hrjob-general-template',
    templateHelpers: function() {
      return {
        'isNew': this.model.get('id') ? false : true,
        'isNewDuplicate': this.model._isDuplicate ? true : false,
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJob
      };
    },
    initialize: function() {
      HRApp.Common.Views.StandardForm.prototype.initialize.apply(this, arguments);
      this.listenTo(this.options.collection, 'sync', this.toggleIsPrimary);
    },
    onRender: function() {
      HRApp.Common.Views.StandardForm.prototype.onRender.apply(this, arguments);
      this.toggleIsPrimary();
    },
    onBindingCreate: function(bindings) {
      bindings.is_primary = {
        selector: 'input[name=is_primary]',
        converter: HRApp.Common.convertCheckbox
      };
      bindings.is_tied_to_funding = {
        selector: 'input[name=is_tied_to_funding]',
        converter: HRApp.Common.convertCheckbox
      };
    },
    /**
     * Define form validation rules
     *
     * @param View view the view for which validation rules are created
     * @param Object r the validation rules for the view
     */
    onValidateRulesCreate: function(view, r) {
      _.extend(r.rules, {
        title: {
          required: true
        },
        position: {
          required: true
        },
        contract_type: {
          required: true
        },
        notice_amount: {
          number: true
        },
        period_start_date: {
          dateISO: true
        },
        period_end_date: {
          dateISO: true
        }
      });
    },
    /**
     * Activate or de-activate is_primary field. If there's only
     * one job, then it must be primary. If a job is already
     * primary, then it's futile to uncheck it (because the
     * API will re-pick it when auto-assigning a primary).
     */
    toggleIsPrimary: function() {
      var jobCount = this.options.collection.length;
      if (!this.options.collection.get(this.model)) {
        jobCount++;
      }
      if (jobCount <= 1) {
        this.$('.hrjob-is_primary-row').hide();
      } else {
        this.$('.hrjob-is_primary-row').show();
      }
      if (this.model.get('is_primary') == '1') {
        this.$('[name=is_primary]').attr('disabled', true);
      } else {
        this.$('[name=is_primary]').attr('disabled', false);
      }
    }
  });
});
