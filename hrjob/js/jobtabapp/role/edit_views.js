// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRApp.module('JobTabApp.Role', function(Role, HRApp, Backbone, Marionette, $, _) {
  Role.RowView = Marionette.Layout.extend({
    bindingAttribute: 'data-hrjobrole-row',
    tagName: 'tr',
    template: '#hrjob-role-row-template',
    templateHelpers: function() {
      return {
        'cid': this.model.cid,
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJobRole
      };
    },
    regions: {
      toggledRegion: '.toggle-role-form'
    },
    events: {
      'click .hrjob-role-remove': 'toggleSoftDelete',
      'click .hrjob-role-restore': 'toggleSoftDelete',
      'click .hrjob-role-toggle': 'toggleRole',
      'click .hrjob-role-funder-add': 'doAddFunder'
    },
    modelEvents: {
      'softDelete': 'renderSoftDelete'
    },
    initialize: function() {
      CRM.HRApp.Common.mbind(this);
    },
    onRender: function() {
      this.$('.hrjob-role-toggle').addClass('closed');
      this.$('.toggle-role-form').hide();
      this.renderSoftDelete();
      var suffix = '_' + this.model.cid;
      var editView = new Role.EditView({
        model: this.model
      });
      this.toggledRegion.show(editView);
      if (this.model.get('funder')) {
        var view = this,
          percentExpr = this.model.get('percent_pay_funder'),percentFunders = null,
	  funderExpr = this.model.get('funder'),funders = null, $i = 0, $j = 0, percentRelFunder=[],percentRel=[],percentAndFunder = '';
        if (funderExpr) {
          funders = funderExpr.split(',');
        }
        if (percentExpr) {
          percentFunders = percentExpr.split(',');
          _.each(percentFunders, function(percentfunderExpr){
            percentAndFunder = percentfunderExpr.split('-');
            percentRelFunder[$j] = percentAndFunder[0];
            percentRel[$j] =  percentAndFunder[1];
            $j += 1;
          });
        }
        _.each(funders, function(funderId){
          var oldFunderNo = view.$('.funderTableBody > tr').last().attr('data-funder-no'), newFunderNo = 1;
          if (oldFunderNo) {
            newFunderNo = parseInt(oldFunderNo) + 1;
          }
          view.model.set('fid',newFunderNo);
          view.model.set('funders-'+$i+''+suffix, funderId);
          percentfunderId = percentRelFunder.indexOf(funderId);
          view.model.set('percent_pay_funder-'+$i+''+suffix, percentRel[percentfunderId]);
          $i += 1;
          if ($i > 1) {
	    var editfunderView = new Role.RowFunderView({
              model: view.model
	    });
	    editfunderView.$el.insertAfter(view.$('.hrjob-role-funder-table tr').last());
	    editfunderView.render();
            view.$('.funderTableBody > tr').last().attr('data-funder-no', newFunderNo);
	  }
        });
	view.model._modified = false;
      }
      this.payAssign();

      if (!this.model.get('funder')) {
        this.$('input[name="percent_pay_funder-0'+suffix+'"]').val(100);
      }
    },
    renderSoftDelete: function() {
      this.$el
        .toggleClass('deleted', this.model.isSoftDeleted())
        .toggleClass('undeleted', !this.model.isSoftDeleted());
    },
    toggleSoftDelete: function() {
      this.model.setSoftDeleted(!this.model.isSoftDeleted());
    },
    toggleRole: function() {
      var open = this.$('.hrjob-role-toggle').hasClass('closed');
      if (open) {
        this.render();
      }
      this.$('.hrjob-role-toggle').toggleClass('open', open).toggleClass('closed', !open);
      this.$('.toggle-role-form').toggle(open);
    },
    onValidateRulesCreate: function(view, r) {
      var suffix = '_' + this.model.cid;
      r.rules['percent_pay_role' + suffix] = {
        required: true,
        number: true
      };
    },
    doAddFunder: function(e) {
      e.stopPropagation();
      var view = this;
      var oldFunderNo = view.$('.funderTableBody > tr').last().attr('data-funder-no'), newFunderNo = 1;
      if (oldFunderNo) {
        newFunderNo = parseInt(oldFunderNo) + 1;
      }
      view.model.set('fid',newFunderNo);
      var editfunderView = new Role.RowFunderView({
        model: view.model
      });
      editfunderView.$el.insertAfter(view.$('.hrjob-role-funder-table tr').last());
      editfunderView.render();
      view.$('.funderTableBody > tr').last().attr('data-funder-no', newFunderNo);
      this.payAssign();
      return false;
    },
    payAssign: function() {
      var view = this;
      var payCollection = new CRM.HRApp.Entities.HRJobPayCollection([], {
        crmCriteria: {contact_id: CRM.jobTabApp.contact_id, job_id: this.model.get('job_id')},
      });
      payCollection.fetch({
        success: function(e) {
          var pay = payCollection.first(),
            totalAmnt = 0,
            totalPercent = 0,
            totalPay = 0;
          if (pay && pay.get("is_paid") == 1 ) {
            $('.funderPerc').each(function(i, obj) {
              var val = $(this).val(),
                payVal = 0;
              if (!val || !$.isNumeric(val)) {
                val  = 0;
                $(this).val(0);
              }
              var payVal = parseInt(val) * pay.get("pay_annualized_est") / 100;
              totalPay = pay.get("pay_currency")+' '+payVal+' per Year';
              $(this).parent().next('td').find('input').val(totalPay);
              $(this).on("keyup", function() {
                var val = $(this).val(),
                  payVal = 0;
                if (!val || !$.isNumeric(val)) {
                  val  = 0;
                  $(this).val(0);
                }
                var payVal = parseInt(val) * pay.get("pay_annualized_est") / 100;
                totalPay = pay.get("pay_currency")+' '+payVal+' per Year';
                $(this).parent().next('td').find('input').val(totalPay);
              });
            });
          }
        },
      });
    },
    onBindingCreate: function(bindings) {
      // The field names in each <TR> must be distinct, so we append the cid.
      // However, ModelBinder doesn't know about the cid suffix, so we fix it.
      var suffix = '_' + this.model.cid,
        funderAttr = 'funders-0',
        fundStr = null,
        percentStr = null,
        percentAttr = 'percent_pay_funder-0',
        $i = 0, $j = 0, relatedFunder = null, funderAll = [], percentAll = [];
      for(attr in this.model.attributes){
        if (attr == funderAttr) {
          bindings[attr] = bindings[attr + suffix];
          delete bindings[attr + suffix];
        }
        else if (attr == percentAttr) {
          bindings[attr] = bindings[attr + suffix];
          delete bindings[attr + suffix];
        }
      }
    }
  });

  Role.RowFunderView = Marionette.Layout.extend({
    tagName: 'tr',
    template: '#hrjob-role-funder-template',
    templateHelpers: function() {
      return {
	'fid': this.model.get('fid'),
        'cid': this.model.cid,
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJobRole
      };
    },
    initialize: function() {
      CRM.HRApp.Common.mbind(this);
    },
    onRender: function () {
      $(this.$el).trigger('crmLoad');
    },
    onShow: function() {
      $(this.$el).trigger('crmLoad');
    }
  });

  Role.EditView = Marionette.ItemView.extend({
    template: '#hrjob-role-template',
    templateHelpers: function() {
      return {
        'cid': this.model.cid,
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJobRole
      };
    },
    initialize: function() {
      CRM.HRApp.Common.mbind(this);
    },
    onRender: function() {
      $(this.$el).trigger('crmLoad');
      var view = this,
        suffix = '_' + this.model.cid,
        payCollection = new CRM.HRApp.Entities.HRJobPayCollection([], {
          crmCriteria: {contact_id: CRM.jobTabApp.contact_id, job_id: this.model.get('job_id')},
        });
      payCollection.fetch({
        success: function(e) {
          var pay = payCollection.first(),
            totalAmnt = 0,
            totalPercent = 0,
            totalPay = 0;
          if (pay && pay.get("is_paid") == 1 ) {
            totalPay = pay.get("pay_currency")+' '+pay.get("pay_annualized_est")+' per Year';
            $('input[name="total_pay_amount"]').val(pay.get("pay_annualized_est"));
            $('input[name="total_pay"]').val(totalPay);
            totalPercent =  view.actualPayToRole();
            totalAmnt = pay.get("pay_currency")+' '+parseFloat(totalPercent)+' per Year';
            $('input[name="actual_amount"]').val(totalAmnt);
            view.$('[name="percent_pay_role'+suffix+'"]').on("keyup", function() {
              totalPercent =  view.actualPayToRole();
              totalAmnt = pay.get("pay_currency")+' '+parseFloat(totalPercent)+' per Year';
              $('input[name="actual_amount"]').val(totalAmnt);
            });
          }
        },
      });
    },
    actualPayToRole: function() {
      var suffix = '_' + this.model.cid,
        totalPay = $('input[name="total_pay_amount"]').val(),
        percentPay = $('input[name="percent_pay_role'+suffix+'"]').val(),
        totalPercent = parseInt(totalPay) * parseInt(percentPay) / 100;
      return totalPercent;
    },
    onBindingCreate: function(bindings) {
      // The field names in each <TR> must be distinct, so we append the cid.
      // However, ModelBinder doesn't know about the cid suffix, so we fix it.
      var suffix = '_' + this.model.cid;
	_.each(['percent_pay_role'], function(field) {
        bindings[field] = bindings[field + suffix];
        delete bindings[field + suffix];
      });
    },
    onShow: function() {
      $(this.$el).trigger('crmLoad');
    }
  });

  Role.TableView = Marionette.CompositeView.extend({
    itemView: Role.RowView,
    itemViewContainer: 'table.hrjob-role-table > tbody',
    template: '#hrjob-role-table-template',
    templateHelpers: function() {
      return {
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJobRole
      };
    },
    events: {
      'click .hrjob-role-add': 'doAdd',
      'click .standard-save': 'doSave',
      'click .standard-reset': 'doReset'
    },
    initialize: function() {
      this.listenTo(HRApp, 'navigate:warnings', this.onNavigateWarnings);
    },
    onRender: function() {
      var view = this;
      var rules = this.createValidationRules();
      view.$('form').validate(rules);
      if (CRM.jobTabApp.isLogEnabled) {
        this.$('.hrjob-revision-link').crmRevisionLink({
          reportId: CRM.jobTabApp.loggingReportId,
          contactId: CRM.jobTabApp.contact_id,
          tableName: this.$('.hrjob-revision-link').attr('data-table-name')
        });
      } else {
        this.$('.hrjob-revision-link').hide();
      }
    },
    createValidationRules: function() {
      var rules = _.extend({}, CRM.validate.params);
      rules.rules || (rules.rules = {});
      this.triggerMethod("validateRules:create", this, rules);
      _.each(this.children.toArray(), function(child) {
        child.triggerMethod("validateRules:create", child, rules);
      });
      return rules;
    },
    funderStat: function() {
      var fundTotal = 0;
      _.forEach(this.collection.models, function (model) {
        var suffix = '_' + model.cid, fundTemp = 0,
          percentAttr = 'percent_pay_funder-0'+suffix;
          percentStr = null,fundStr = null,
          funderAttr = 'funders-0'+suffix,
	  $i = 0, $j = 0, relatedFunder = null;
        for(attr in model.attributes){
          if (attr == percentAttr) {
            fundTemp = model.get(attr);
            fundTotal += parseInt(fundTemp);
            $i += 1;
            percentAttr = 'percent_pay_funder-'+ $i+''+suffix;
          }
        }
      });
      if (parseInt(fundTotal) > 100) {
        return false;
      }
      return true;
    },
    payStat: function() {
      var payTotal = 0;
      _.forEach(this.collection.models, function (model) {
        var payTemp = 0;
        payTemp = model.get('percent_pay_role');
	payTotal += parseInt(payTemp);
      });
      if (parseInt(payTotal) > 100) {
        return false;
      }
      return true;
    },
    hourStat: function() {
      var view =this,
        hourInfo = view.options.hourInfo,
        actualHour = view.hourCalculation(hourInfo.hourUnit, hourInfo.hoursType, hourInfo.hourAmount),
	$hoursType = hourInfo.hoursType,
	job_hours_time = CRM.PseudoConstant.job_hours_time,
        addHour = 0, totalHour = 0, hourUnit = null, hourAmnt = 0;
      if (!$hoursType) {
        $hoursType = job_hours_time['Full_Time'];
      }
      _.forEach(view.collection.models, function (model) {
        hourAmnt = model.get('hours');
        hourUnit = model.get('role_hours_unit');
        totalHour = view.hourCalculation(hourUnit, $hoursType, parseInt(hourAmnt));
        addHour = parseInt(addHour) + totalHour;
      });
      if (parseInt(addHour) > parseInt(actualHour)) {
        return false;
      }
      return true;
    },
    hourCalculation: function($hrs_unit, $fullTimeHour, hourAmount) {
      var $working_days = CRM.PseudoConstant.working_days,
        $totalHour = 0,
        $hrs_unit = $hrs_unit || null,
        $hour = 0;
      if ($hrs_unit == 'Day') {
        $hour = $fullTimeHour;
      }
      else if ($hrs_unit == 'Week') {
        $hour = $fullTimeHour * $working_days.perWeek;
      }
      else if ($hrs_unit == 'Month') {
        $hour = $fullTimeHour * $working_days.perMonth;
      }
      else if ($hrs_unit == 'Year') {
        $hour = $fullTimeHour * $working_days.perMonth * 12;
      }
      else if (!$hrs_unit) {
        $hour = 0;
      }
      $totalHour = parseInt(hourAmount) * parseInt($hour);
      return parseInt($totalHour);
    },
    appendHtml: function(collectionView, itemView, index) {
      collectionView.$('tr.hrjob-role-final').before(itemView.el);
    },
    doAdd: function(e) {
      e.stopPropagation();
      var model = new CRM.HRApp.Entities.HRJobRole(
        this.options.newModelDefaults || {}
      );
      this.collection.add(model);
      this.children.findByModel(model).toggleRole(); // open
      return false;
    },
    doSave: function() {
      var view = this,
        rules = this.createValidationRules();
      _.forEach(view.collection.models, function (model) {
        var suffix = '_' + model.cid,
          funderAttr = 'funders-0'+suffix,
          fundStr = null, percentStr = null,
          percentAttr = 'percent_pay_funder-0'+suffix, $i = 0, $j = 0, percentPay = 0,
          relatedFunderPer = 0, funderAll = [], percentAll = [];

        for(attr in model.attributes){
          if (attr == funderAttr) {
            funderAll[$i] = model.get(attr);
            relatedFunderPer = model.get('percent_pay_funder-'+ $i+''+suffix);
            if (!relatedFunderPer) {
              relatedFunderPer = 0;
            }
            if (!funderAll[$i]) {
              relatedFunderPer = 0;
              model.set('percent_pay_funder-'+ $i+''+suffix, 0);
            }
            percentAll[$i] = funderAll[$i] +'-'+ relatedFunderPer;
            $i += 1;
            funderAttr = 'funders-'+ $i+''+suffix;
            percentPay += parseInt(relatedFunderPer);
          }
        }
        fundStr = funderAll.join(',');
        percentStr = percentAll.join(',');
        model.set('funder', fundStr);
        model.set('percent_pay_funder', percentStr);
        model.set('percent_pay_role', percentPay);
      });
      view.$('form').validate(rules);

      if(!this.payStat()) {
        CRM.alert(ts('The sum of the Percent of Pay Assigned for all Roles for a Job Position must never be more than 100'), ts('Invalid Percent of Pay Assigned'), 'error');
        return false;
      }

      if(!this.funderStat()) {
        CRM.alert(ts('The sum of the Percent of Pay Assigned to these Role for all funders for a Job Position must never be more than 100.'), ts('Invalid Percent of Pay Assigned'), 'error');
        return false;
      }

      if(!this.hourStat()) {
        CRM.alert(ts('The sum of the hours for all Roles for a Job Position must never be more than total hours defined'), ts('Invalid Hours'), 'error');
        return false;
      }
      if (!this.$('form').valid()) {
        return false;
      }

      HRApp.trigger('ui:block', ts('Saving'));
      view.collection.save({
        success: function() {
          HRApp.trigger('ui:unblock');
          CRM.alert(ts('Saved'), null, 'success');
          view.render();
          view.triggerMethod('standard:save', view, view.model);
        },
        error: function() {
          HRApp.trigger('ui:unblock');
          // Note: CRM.Backbone.sync displays API errors with CRM.alert
        }
      });
      return false;

    },
    doReset: function() {
      var view = this;
      HRApp.trigger('ui:block', ts('Loading'));
      this.collection.fetch({
        reset: true,
        success: function() {
          HRApp.trigger('ui:unblock');
          CRM.alert(ts('Reset'));
          view.render();
          view.triggerMethod('standard:reset', view, view.model);
        },
        error: function() {
          HRApp.trigger('ui:unblock');
          // Note: CRM.Backbone.sync displays API errors with CRM.alert
        }
      });
      return false;
    },
    onNavigateWarnings: function(route, options) {
      // The "Role" table may include a mix of existing (modifiable) rows,
      // newly added rows, and deleted rows.
      var modified = this.collection.foldl(function(memo, model) {
        return memo || model.isNew() || model.isModified() || model.isSoftDeleted();
      }, false);
      if (modified) {
        options.warnTitle = ts('Abandon Changes?');
        options.warnMessages.push(ts('There are unsaved changes! Are you sure you want to abandon the changes?'));
      }
    }
  });
});
