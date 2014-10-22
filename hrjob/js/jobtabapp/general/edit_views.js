// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
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
	//display contract file
	var entityID = this.model.get('id');
	var fileurl = CRM.url('civicrm/hrjob/file/display');
        $.ajax({ type: "POST",
          url: fileurl,
          async : false,
          data: { entityID: entityID, entityTable: "civicrm_hrjob_general"}
        }).done(function( msg ) {
        $('<div class=contract-file-display>'+msg+'</div>').insertBefore('#contract_file');
        });
	//contract duration autocalculate
	var $duration = this.$('[name=duration]'),
	    $starts = null,
	    $ends = null,
  	    day = null;

	if ($start.val() && $end.val()) {
	  $starts = $start.val();
	  $ends = $end.val();
	  select();
	}
	else {	    
	  $duration.html('No Contract End Date');
	}
        $start.change(function() {
	  $starts = $start.val();
	  select();
        });
	$end.change(function() {
	  $ends = $end.val();
	  select();
	});  
	function select() {
	  if ($starts && $ends) {
	    diff  = (new Date($ends) - new Date($starts)),
	    day  = moment.preciseDiff($starts,$ends);
	    $duration.html(day);
	  }
	  if ($starts == $ends) {	
	    day = '0';
	    $duration.html(day);
	  }
	}
	//automatically update notice period and unit
	var $notice_amount = this.$('[name=notice_amount]');
	var $notice_amount_employee = this.$('[name=notice_amount_employee]');
	if (!$notice_amount.val()) {
	  if ($notice_amount.val() === $notice_amount_employee.val()) {
            $notice_amount.bind("keyup", function() {
	      $notice_amount_employee.val($notice_amount.val());
	      $notice_amount_employee.change();
            });
            $notice_amount_employee.bind("change", function() {
	      if ($notice_amount.val() !== $notice_amount_employee.val()) {
	        $notice_amount.unbind("keyup");
	      }
            });
	  }
	}
	var $notice_unit = this.$("select#hrjob-notice_unit");
	var $notice_unit_employee = this.$("select#hrjob-notice_unit_employee");
	if ($notice_unit.val() === $notice_unit_employee.val()) {
	  $notice_unit.change(function() {
	    $notice_units = $notice_unit.val();
	    if (!$notice_unit_employee.val()) {
	      $("#s2id_hrjob-notice_unit_employee .select2-choice span").first().text($notice_units);
	    }
	  });  
	}
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
      events: {
	  //save,delete contract file
	  'click .standard-save': 'doSave',
	  'click .standard-reset': 'doReset',
	  'click .file-delete > a': 'doDelete',
      },
    doSave: function(){
	var view = this;
	var entityID = this.model.get('id');
	//save notice unit
	var $notice_unit = $("#s2id_hrjob-notice_unit_employee .select2-choice span").first().text();
	if ($notice_unit != '- select -') {
	  for (k in this.model.attributes) {
	    if (k === 'notice_unit_employee')
	      this.model.attributes[k] = $notice_unit;
	    }
	}
      if (!this.$('form').valid() || !view.model.isValid()) {
        return false;
      }
      HRApp.trigger('ui:block', ts('Saving'));
      if (this.$('form input[type=file]') ) {
        var formfile = this.$('form');
        var filedata = new FormData(formfile[0]);
        var fileurl = CRM.url('civicrm/hrjob/file/upload');
        filedata.append("entityID", entityID);
        filedata.append("entityTable", "civicrm_hrjob_general"); 
        $.ajax({ type: "POST",
          url: fileurl,
          data: filedata,
          async : false,
          processData:false,
          contentType: false,
          success: function() {}
        });
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
    doDelete: function() {
      var view = this;
      var entityID = this.model.get('id');
      var fileID = $('.file-delete a').attr('id').split('_');
      var fileurl = CRM.url('civicrm/hrjob/file/delete');
      $.ajax({ type: "POST",
        url: fileurl,
        async : false,
        data: { entityID : entityID, fileID : fileID[1], entityTable: "civicrm_hrjob_general" },
        success: function(html) {
          $('#del_'+fileID[1]).remove();
            var successMsg = ts('The selected attachment has been deleted.');
            CRM.alert(successMsg, ts('Removed'), 'success');
        }
      });
    },



    onBindingCreate: function(bindings) {
      bindings.is_primary = {
        selector: 'input[name=is_primary]',
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
	notice_amount_employee: {
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
