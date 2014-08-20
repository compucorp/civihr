// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRApp.module('JobTabApp.Hour', function(Hour, HRApp, Backbone, Marionette, $, _){
  Hour.EditView = HRApp.Common.Views.StandardForm.extend({
    template: '#hrjob-hour-template',
    templateHelpers: function() {
      return {
        'isNew': this.model.get('id') ? false : true,
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

      var $hours_type = this.$("select#hrjob-hours_type"),
        $full_time_hour = CRM.PseudoConstant.job_hours_time.Full_Time,
        $part_time_hour = CRM.PseudoConstant.job_hours_time.Part_Time,
        $causual_hour = CRM.PseudoConstant.job_hours_time.Casual;
      $hours_type.change(function() {
        $hours_types = $hours_type.val();
        if ($hours_types == $full_time_hour) {
          $("#hrjob-hours_amount").val($full_time_hour);
          $("#s2id_hrjob-hours_unit .select2-choice span").first().text('Day');
          $("#hrjob-hours_fte").val(1.0);
        }
        else if ($hours_types == $part_time_hour) {
          $("#hrjob-hours_amount").val($part_time_hour);
          $("#s2id_hrjob-hours_unit .select2-choice span").first().text('Day');
          $("#hrjob-hours_fte").val(0.5);
        }
        else if ($hours_types == $causual_hour) {
          $("#hrjob-hours_amount").val($causual_hour);
          $("#s2id_hrjob-hours_unit .select2-choice span").first().text('Week');
          $("#hrjob-hours_fte").val(0);
        }
      });
      // Auto calculate FTE
      var $hrs_Amount = this.$('[name=hours_amount]'),
        $hrs_fte = this.$('[name=hours_fte]');
      $hrs_Amount.bind("keyup", function() {
        $total_fte = $hrs_Amount.val()/$full_time_hour;
        $hrs_fte.val($total_fte);
        $hrs_fte.change();
      });
    },
    events: {
      'click .standard-save': 'doSave',
      'click .standard-reset': 'doReset',
    },
    doSave: function(){
      var view = this;
      var $hrs_unit = $("#s2id_hrjob-hours_unit .select2-choice span").first().text();
        $hrs_amt = $("#hrjob-hours_amount").val();
        $hrs_fte = $("#hrjob-hours_fte").val();
      for (k in this.model.attributes) {
        if (k === 'hours_amount') {
          this.model.attributes[k] = $hrs_amt;
        }
        else if (k === 'hours_unit') {
          this.model.attributes[k] = $hrs_unit;
        }
        else if (k === 'hours_fte') {
          this.model.attributes[k] = $hrs_fte;
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
