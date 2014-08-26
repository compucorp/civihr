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
        $causual_hour = CRM.PseudoConstant.job_hours_time.Casual,
        $fullTimeHour = CRM.PseudoConstant.job_hours_time.Full_Time,
        $working_days = CRM.PseudoConstant.working_days;
      $hours_type.change(function() {
        $hours_types = $hours_type.val();
        $("#hrjob-hours_amount").val($hours_types);
        $("#s2id_hrjob-hours_unit .select2-choice span").first().text('Day');
        $("#hrjob-hours_unit").val('Day');
        if ($hours_types == $full_time_hour) {
          $("#hrjob-fte_num").val('1');
          $("#hrjob-fte_denom").val('1');
        }
        else if ($hours_types == $part_time_hour) {
          $("#hrjob-fte_num").val('1');
          $("#hrjob-fte_denom").val('2');
        }
        else if ($hours_types == $causual_hour) {
          $("#s2id_hrjob-hours_unit .select2-choice span").first().text('Week');
          $("#hrjob-hours_unit").val('Week');
          $("#hrjob-fte_num").val('0');
          $("#hrjob-fte_denom").val('1');
        }
      });

      function changeVal() {
        var $hrs_unit = $("#s2id_hrjob-hours_unit .select2-choice span").first().text();
        //HR-396 - Calucation for denominator value from hour type option group
        if ($hrs_unit == 'Day') {
          $totalHour = $fullTimeHour;
        }
        else if ($hrs_unit == 'Week') {
          $totalHour = $fullTimeHour * $working_days.perWeek;
        }
        else if ($hrs_unit == 'Month') {
          $totalHour = $fullTimeHour * $working_days.perMonth;
        }
        else if ($hrs_unit == 'Year') {
          $totalHour = $fullTimeHour * $working_days.perMonth * 12;
        }
        $('input[name=fte_num]').val($('input[name=hours_amount]').val());
        $('input[name=fte_denom]').val($totalHour);
      }
      this.$('[name=hours_amount]').bind("keyup", function() {
        changeVal();
      });

      this.$('[name=hours_unit]').bind("change", function() {
        changeVal();
      });
    },
    events: {
      'click .standard-save': 'doSave',
      'click .standard-reset': 'doReset',
    },
    doSave: function(){
      var view = this;
      //check whether form is validate
      if (!this.$('form').valid() || !view.model.isValid()) {
        return false;
      }
      var $hrs_unit = $("#s2id_hrjob-hours_unit .select2-choice span").first().text();
        $hrs_amt = $("#hrjob-hours_amount").val();
        $fte_num = $("#hrjob-fte_num").val();
        $fte_denom = $("#hrjob-fte_denom").val();
        $total_fte = $fte_num/$fte_denom;

      // Reduce a fraction by finding the Greatest Common Divisor and dividing by it.
      function reduce(numerator,denominator) {
        var gcd = function gcd(a,b) {
          return b ? gcd(b, a%b) : a;
        };
        gcd = gcd(numerator,denominator);
        return [numerator/gcd, denominator/gcd];
      }
      $lowfraction = reduce ($fte_num,$fte_denom);
      for (k in this.model.attributes) {
        if (k === 'hours_amount') {
          this.model.attributes[k] = $hrs_amt;
        }
        else if (k === 'hours_unit') {
          this.model.attributes[k] = $hrs_unit;
        }
        else if (k === 'hours_fte') {
          this.model.attributes[k] = $total_fte;
        }
        else if (k === 'fte_num') {
          this.model.attributes[k] = $lowfraction[0];
        }
        else if (k === 'fte_denom') {
          this.model.attributes[k] = $lowfraction[1];
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
        fte_num: {
          required: true,
          digits: true
        },
        fte_denom: {
          required: true,
          digits: true,
          min:1
        }
      });
    }
  });
});
