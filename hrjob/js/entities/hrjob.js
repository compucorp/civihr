// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRApp.module('Entities', function(Entities, HRApp, Backbone, Marionette, $, _){
  Entities.HRJob = Backbone.Model.extend({
    defaults: {
      contact_id: null,
      position: '',
      title: '',
      funding_notes: '',
      contract_type: null,
      department: null,
      level_type: null,
      period_type: null,
      period_start_date: '',
      period_end_date: '',
      notice_amount: '',
      notice_unit: '',
      notice_amount_employee: '',
      notice_unit_employee: '',
      manager_contact_id: null,
      is_primary: 0,
      contract_file: null
    },

    isActive: function() {
      if (this.get('period_end_date')) {
        // Don't use Date() comparison - don't trust TZ handling
        var now = new Date();
        var nowParts = [ now.getFullYear(), (now.getMonth()+1), now.getDate() ];
        var endDateParts = this.get('period_end_date').split("-");
        for (var i = 0; i< 3; i++) {
          if (parseInt(nowParts[i]) > parseInt(endDateParts[i])) return false;
          if (parseInt(nowParts[i]) < parseInt(endDateParts[i])) return true;
          if (parseInt(nowParts[i]) == parseInt(endDateParts[i])) continue;
          throw ("Malformed date");
        }
        return true; // today is the last day!
      } else {
        return true; // no end date specified
      }
    },

    validate: function(attrs, options) {
      var errors = {}
      if (! attrs.position) {
        errors.position = ts("Field is required");
      }
      if( ! _.isEmpty(errors)){
        return errors;
      }
    }
  });
  CRM.Backbone.extendModel(Entities.HRJob, 'HRJob');
  CRM.Backbone.trackSaved(Entities.HRJob);
  CRM.Backbone.trackSoftDelete(Entities.HRJob);

  Entities.HRJobCollection = Backbone.Collection.extend({
    sync: CRM.Backbone.sync,
    model: Entities.HRJob,
    comparator: function(model) {
      return (model.get('is_primary') == '1' ? 'a' : 'b')
        + "::" + (model.isActive() ? "a" : "b")
        + "::" + model.get("contract_type")
        + "::" + model.get("position");
    }
  });
  CRM.Backbone.extendCollection(Entities.HRJobCollection);

  Entities.HRJobRole = Backbone.Model.extend({
    defaults: {
      job_id: null,
      title: '',
      description: '',
      hours: 0,
      role_hours_unit: null,
      region: '',
      department: null,
      manager_contact_id: null,
      functional_area: '',
      organization: '',
      cost_center: '',
      percent_pay_role: '100',
      funder: null,
      location: ''
    }
  });

  Entities.HRJobHealth = Backbone.Model.extend({
    defaults: {
      job_id: null,
      provider: null,
      plan_type: '',
      description: '',
      dependents: '',
      provider_life_insurance: null,
      plan_type_life_insurance:'',
      description_life_insurance:'',
      dependents_life_insurance:''
    }
  });

  Entities.HRJobHour = Backbone.Model.extend({
    defaults: {
      job_id: null,
      hours_type: '',
      hours_amount: '',
      hours_unit: '',
      hours_fte: '',
      fte_num: '',
      fte_denom: ''
    }
  });

  Entities.HRJobPay = Backbone.Model.extend({
    defaults: {
      job_id: null,
      is_paid: '',
      pay_amount: '',
      pay_unit: '',
      pay_currency: '',
      pay_annualized_est: '',
      pay_is_auto_est: 1
    }
  });

  Entities.HRJobPension = Backbone.Model.extend({
    defaults: {
      job_id: null,
      is_enrolled: '',
      er_contrib_pct: '',
      ee_contrib_pct: '',
      pension_type:'',
      ee_contrib_abs: ''
    }
  });

  Entities.Setting = Backbone.Model.extend({
    // Restrict returned settings to mitigate risk that concurrent processes CRUD the same setting
    crmReturn: ['work_months_per_year','work_weeks_per_year','work_days_per_week','work_days_per_month', 'work_days_per_month'],
    defaults: {}
  });

  Entities.HRJobLeave = Backbone.Model.extend({
    defaults: {
      leave_amount: 0
    },
    validate: function(attrs, options) {
      var errors = {};

      if (! attrs.leave_amount) {
        errors.leave_amount = ts("Field is required");
      } else if (!_.isNumber(attrs.leave_amount)) {
        errors.leave_amount = ts("Not a number");
      }

      if (!_.isEmpty(errors)) {
        return errors;
      }
    }
  });

  Entities.HRJobLeaveCollection = Backbone.Collection.extend({
    model: Entities.HRJobLeave,
    comparator: function(model) {
      return model.get('leave_type');
    },
    /**
     * @param expected list of expected leave types (strings)
     * @param defaults list of values to put in any newly created leave records (key-value pairs)
     */
    addMissingTypes: function(expected, defaults) {
      defaults || (defaults = {});
      var coll = this;
      var existing = this.pluck('leave_type');
      var missing = _.difference(expected, existing);
      _.each(missing, function(missingLeaveType) {
        var attrs = _.extend({}, defaults, {
          leave_type: missingLeaveType,
          leave_amount: 0
        });
        var model = new Entities.HRJobLeave(attrs);
        coll.add(model);
      });
    }
  });

  // FIXME real models
  _.each(['HRJobHealth', 'HRJobHour', 'HRJobLeave', 'HRJobPay', 'HRJobPension', 'HRJobRole', 'Setting'], function(entityName){
    if (!Entities[entityName]) {
      Entities[entityName] = Backbone.Model.extend({});
    }
    CRM.Backbone.extendModel(Entities[entityName], entityName);
    CRM.Backbone.trackSaved(Entities[entityName]);
    CRM.Backbone.trackSoftDelete(Entities[entityName]);

    if (!Entities[entityName + "Collection"]) {
      Entities[entityName + "Collection"] = Backbone.Collection.extend({
        model: Entities[entityName]
      });
    }
    CRM.Backbone.extendCollection(Entities[entityName + "Collection"]);
  });
});
