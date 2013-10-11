// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRApp.module('JobTabApp.Funding', function(Funding, HRApp, Backbone, Marionette, $, _) {
	Funding.EditView = HRApp.Common.Views.StandardForm.extend({
    template: '#hrjob-funding-template',
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

      var dateOptions = {
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true,
        yearRange: '-100:+20'
      };
      var $start = this.$('[name=period_start_date]');
      var $end = this.$('[name=period_end_date]');
      $start
        .addClass('dateplugin')
        .datepicker(_.extend({}, dateOptions, {
          maxDate: $end.val() ? $end.val() : null,
          onClose: function(selectedDate) {
            $end.datepicker("option", "minDate", selectedDate);
          }
        }));
      $end
        .addClass('dateplugin')
        .datepicker(_.extend({}, dateOptions, {
          minDate: $start.val() ? $start.val() : null,
          onClose: function(selectedDate) {
            $start.datepicker("option", "maxDate", selectedDate);
          }
        }));
      /*
       .addClass('dateplugin')
       });*/
      //hrjob: automatically update the "Job Title"
      var $position = this.$('[name=position]');
      var $title = this.$('[name=title]');
      if($position.val() === $title.val()) {
        $position.bind("keyup", function() {
    	  $title.val($position.val());
    	  $title.change();
        });
        $title.bind("change", function() {
         if($position.val() !== $title.val()) {	
    	   $position.unbind("keyup");
         } 
    	});
      }
    },
    onBindingCreate: function(bindings) {
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

      var disabled = false;
      if (this.model.get('is_primary') == '1') {
        disabled = true;
      }
      if (!this.model.isActive()) {
        var hasActiveModel = this.options.collection.reduce(function(memo, model){return memo||model.isActive()}, false);
        if (hasActiveModel) {
          disabled = true;
        }
      }
      this.$('[name=is_primary]').attr('disabled', disabled);
    }
  });
});
