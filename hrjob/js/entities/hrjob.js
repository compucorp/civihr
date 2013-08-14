CRM.HRApp.module('Entities', function(Entities, HRApp, Backbone, Marionette, $, _){
  Entities.HRJob = Backbone.Model.extend({
    defaults: {
      contact_id: null,
      position: '',
      title: '',
      is_tied_to_funding: 0,
      contract_type: null,
      level_type: null,
      period_type: null,
      period_start_date: '',
      period_end_date: '',
      manager_contact_id: null,
      is_primary: 0
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
      region: '',
      department: '',
      manager_contact_id: null,
      functional_area: '',
      organization: '',
      cost_center: '',
      location: ''
    }
  });

  Entities.HRJobHealth = Backbone.Model.extend({
    defaults: {
      job_id: null,
      provider: '',
      plan_type: '',
      description: '',
      dependents: ''
    }
  });

  Entities.HRJobHour = Backbone.Model.extend({
    defaults: {
      job_id: null,
      hours_type: '',
      hours_amount: '',
      hours_unit: '',
      hours_fte: ''
    }
  });

  Entities.HRJobPay = Backbone.Model.extend({
    defaults: {
      job_id: null,
      pay_grade: '',
      pay_amount: '',
      pay_unit: ''
    }
  });

  Entities.HRJobPension = Backbone.Model.extend({
    defaults: {
      job_id: null,
      is_enrolled: '',
      contrib_pct: ''
    }
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
  _.each(['HRJobHealth', 'HRJobHour', 'HRJobLeave', 'HRJobPay', 'HRJobPension', 'HRJobRole'], function(entityName){
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
