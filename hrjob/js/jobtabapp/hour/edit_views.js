CRM.HRApp.module('JobTabApp.Hour', function(Hour, HRApp, Backbone, Marionette, $, _){
  Hour.EditView = HRApp.Common.Views.StandardForm.extend({
    template: '#hrjob-hour-template',
    templateHelpers: function() {
      return {
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJobHour
      };
    },
    modelEvents: _.extend({}, HRApp.Common.Views.StandardForm.prototype.modelEvents, {
      'change:hours_type': 'toggleFields'
    }),
    onRender: function() {
      HRApp.Common.Views.StandardForm.prototype.onRender.apply(this, arguments);
      if (this.model.get('hours_type')) {
        this.$('.hrjob-needs-type').show();
      } else {
        this.$('.hrjob-needs-type').hide();
      }
    },
    toggleFields: function() {
      var view = this;
      if (this.model.get('hours_type')) {
        view.$('.hrjob-needs-type:hidden').slideDown({
          complete: function() {
            view.$('[name=hours_amount]').focus();
          }
        });
      } else {
        view.$('.hrjob-needs-type').slideUp();
      }
    },
    /**
     * Define form validation rules
     *
     * @param View view the view for which validation rules are created
     * @param Object r the validation rules for the view
     */
    onValidateRulesCreate: function(view, r) {
      _.extend(r.rules, {
        hours_amount: {
          required: true,
          number: true
        },
        hours_unit: {
          required: true
        },
        hours_fte: {
          required: true,
          number: true,
          range: [0, 2]
        }
      });
    }
  });
});
