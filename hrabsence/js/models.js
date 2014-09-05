// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRAbsenceApp.module('Models', function(Models, HRAbsenceApp, Backbone, Marionette, $, _) {
  Models.Absence = Backbone.Model.extend({
    initialize: function(options) {
      this.listenTo(this, 'change:activity_date_time', this.calculatePeriodId);
    },
    isInPeriod: function(period) {
      var effectiveDateTime = this.get('absence_range').low;
      if (!effectiveDateTime) effectiveDateTime =this.get('activity_date_time');

      var effectiveMoment = CRM.HRAbsenceApp.moment(effectiveDateTime);
      if (effectiveMoment.isBefore(CRM.HRAbsenceApp.moment(period.start_date), 'day')) return false;
      if (effectiveMoment.isAfter(CRM.HRAbsenceApp.moment(period.end_date), 'day')) return false;
      return true;
    },
    calculatePeriodId: function() {
      for (period in CRM.absenceApp.periods) {
        if (this.isInPeriod(CRM.absenceApp.periods[period])) {
          this._periodId = CRM.absenceApp.periods[period].id;
          return;
        }
      }
      this._periodId = null;
      if (console.log) console.log("Failed to determine period: " + this.get('activity_date_time'));
      throw "Failed to determine period: " + this.get('activity_date_time');
    },
    getPeriodId: function() {
      if (!this._periodId) this.calculatePeriodId();
      return this._periodId;
    },
    getFormattedDuration: function() {
      if (this.get('absence_range') && CRM.HRAbsenceApp.absenceTypeCollection) {
        var val = 0;
        if (this.get('absence_range').approved_duration) {
          var val = parseInt(this.get('absence_range').approved_duration);
        }
        else {
          var val = parseInt(this.get('absence_range').duration);
        }
        return CRM.HRAbsenceApp.formatDuration(val * CRM.HRAbsenceApp.absenceTypeCollection.findDirection(this.get('activity_type_id')));
      } else {
        return '';
      }
    }
  });
  CRM.Backbone.extendModel(Models.Absence, 'Activity');

  Models.AbsenceCollection = Backbone.Collection.extend({
    model: Models.Absence,

    /**
     * Calculate a subtotal of all durations
     * @param filter
     * @return {Float} add up the
     */
    calculateSubtotal: function(filter)  {
      var duration = 0;
      this.each(function(absence){
        if (filter(absence)) {
          duration = duration + parseFloat(absence.getFormattedDuration());
        }
      });
      return duration;
    },

    /**
     * Create a listing of absennce-requests, sorted by the actual dates on which
     * the absence was claimed. Note that a given absence may appear multiple times.
     *
     * @return Object keys are dates; each item is an array of AbsenceModel
     */
    createDateIndex: function() {
      var idx = {};
      this.each(function(activity) {
        _.each(activity.get('absence_range').items, function(absenceItem) {
          var date = CRM.HRAbsenceApp.moment(absenceItem.activity_date_time).format('YYYY-MM-DD');
          if (!idx[date]) {
            idx[date] = [];
          }
          idx[date].push(activity);
        });
      });
      return idx;
    },

    /**
     * Generate statistics about a month based on the listed absences
     *
     * @return Object keys are month-codes ("YYYY-MM"); each item is an object with properties:
     *  - creditCount: the #credits
     *  - creditTotal: the sum of credits in the month
     *  - debitCount: the #debits
     *  - debitTotal: the sum of debits in the month
     */
    createMonthStats: function() {
      var stats = {};
      this.each(function(activity) {
        _.each(activity.get('absence_range').items, function(absenceItem){
          var month = CRM.HRAbsenceApp.moment(absenceItem.activity_date_time).format('YYYY-MM');
          if (!stats[month]) {
            stats[month] = {
              creditCount: 0,
              creditTotal: 0,
              debitCount: 0,
              debitTotal: 0
            };
          }
          var sign = CRM.HRAbsenceApp.absenceTypeCollection ? CRM.HRAbsenceApp.absenceTypeCollection.findDirection(activity.get('activity_type_id')) : 0;
          if (sign == -1) {
            stats[month].debitTotal = stats[month].debitTotal + parseInt(absenceItem.duration);
            stats[month].debitCount++;
          } else if (sign == 1) {
            stats[month].creditTotal = stats[month].creditTotal + parseInt(absenceItem.duration);
            stats[month].creditCount++;
          } else {
            if (console.log) console.log('Failed to determine direction', CRM.HRAbsenceApp.absenceTypeCollection, activity);
          }
        });
      });
      return stats;
    },

    /** @return array of type-ids (int) */
    findActiveActivityTypes: function() {
      return _.uniq(
        this.map(function(model) {
          return model.get('activity_type_id');
        })
      );
    },

    /** @return array of period-ids (int) */
    findActivePeriods: function() {
      var coll = this;
      var periodIds = [];
      _.each(CRM.absenceApp.periods, function(period) {
        for (key in coll.models) {
          if (coll.models[key].isInPeriod(period)) {
            periodIds.push(period.id);
            return;
          }
        }
      });
      return periodIds;
    }
  });
  CRM.Backbone.extendCollection(Models.AbsenceCollection);

  Models.AbsenceType = Backbone.Model.extend({});
  CRM.Backbone.extendModel(Models.AbsenceType, 'HRAbsenceType');
  Models.AbsenceTypeCollection = Backbone.Collection.extend({
    model: Models.AbsenceType,

    /**
     * Find the absence-type based on its debit_activity_type_id
     *
     * @param int actId
     * @return AbsenceType|null
     */
    findByDebitTypeId: function(actId) {
      return this.find(function(absenceType){
        return absenceType.get('debit_activity_type_id') == actId;
      });
    },
    /**
     * Determine if the activity type is a credit or debit
     * @param activityTypeId
     * @return int|null -1 (debit), +1 (credit)
     */
    findDirection: function(activityTypeId) {
      var absType = this.findAbsenceType(activityTypeId);
      if (absType) {
        if (absType.get('allow_debits') == 1 && absType.get('debit_activity_type_id') == activityTypeId) {
          return -1;
        }
        if (absType.get('allow_credits') == 1 && absType.get('credit_activity_type_id') == activityTypeId) {
          return 1;
        }
      }
      return null;
    },
    /**
     * Find the absence-type which defines the given activity-type
     *
     * @param int activityTypeId
     * @return AbsenceTypeModel|null
     */
    findAbsenceType: function(activityTypeId) {
      var absTypes = this.getAbsenceTypes();
      return absTypes[activityTypeId] ? this.get(absTypes[activityTypeId]) : null;
    },
    /**
     *
     * @return {Object} keys are activity-type-ids; values are absence-type id's
     */
    getAbsenceTypes: function() {
      // TODO cache
      var idx = {};
      this.each(function(model) {
        if (model.get('allow_debits') == 1) {
          idx[model.get('debit_activity_type_id')] = model.get('id');
        }
        if (model.get('allow_credits') == 1) {
          idx[model.get('credit_activity_type_id')] = model.get('id');
        }
      });
      return idx;
    }
  });
  CRM.Backbone.extendCollection(Models.AbsenceTypeCollection);

  Models.Entitlement = Backbone.Model.extend({
    getFormattedAmount: function() {
      return this.get('amount') ? ('+' + parseFloat(this.get('amount')).toFixed(2)) : '';
    }
  });
  CRM.Backbone.extendModel(Models.Entitlement, 'HRAbsenceEntitlement');
  Models.EntitlementCollection = Backbone.Collection.extend({
    model: Models.Entitlement,

    /**
     *
     * @param int|Model absenceType
     * @param int|Model period
     * @return Entitlement|undefined
     */
    findByPeriod: function(period) {
      if (!period) return undefined;
      var periodId = (_.isObject(period)) ? period.get('id') : period;
      return this.find(function(entitlement){
        return entitlement.get('period_id') == periodId;
      });
    },
    /**
     *
     * @param int|Model absenceType
     * @param int|Model period
     * @return Entitlement|undefined
     */
    findByTypeAndPeriod: function(absenceType, period) {
      if (!absenceType) return undefined;
      var absTypeId = (_.isObject(absenceType)) ? absenceType.get('id') : absenceType;
      var periodId = (_.isObject(period)) ? period.get('id') : period;
      return this.find(function(entitlement){
        return entitlement.get('type_id') == absTypeId && entitlement.get('period_id') == periodId;
      });
    },
    /**
     * Get list of entitlement amounts (indexed by absence-type and period)
     * @return {Object} e.g. result[absence_type_id][period_id] = amount
     */
    getEntitlements: function() {
      var idx = {};
      this.each(function(model) {
        var absTypeId = model.get('type_id');
        var pid = model.get('period_id');
        if (!idx[absTypeId]) {
          idx[absTypeId] = {};
        }
        idx[absTypeId][pid] = model.get('amount');
      });
      return idx;
    }
  });
  CRM.Backbone.extendCollection(Models.EntitlementCollection);

  Models.JobLeaves = Backbone.Model.extend({});
  CRM.Backbone.extendModel(Models.JobLeaves, 'HRJob');
  Models.JobLeavesCollection = Backbone.Collection.extend({
    model: Models.JobLeaves,
    /**
     * Get list of contract position and entitlement info (indexed by job ID)
     * @return {Object} e.g. result[job_id]
     */
    getContractLeaves: function() {
      var stats = {};
      this.each(function(model) {
        var apiparamsget = model.get('api.HRJobLeave.get');
        _.each(apiparamsget.values, function(contractIds, contractVals) {
          var statsKey = model.get('id');
          if (!stats[statsKey]) {
            stats[statsKey] = {
              position: model.get('position'),
              start_date: model.get('period_start_date'),
              end_date: model.get('period_end_date')
            };
          }
          stats[statsKey][contractIds.leave_type] = contractIds.leave_amount;
        });
      });
      return stats;
    },
    getPrimaryJobID: function() {
      var stats = null;
      this.each(function(model) {
        var primeID = model.get('is_primary');
	  if (primeID == 1) {
	   stats = model.get('id');
	  }
      });
      return stats;

    }
  });
  CRM.Backbone.extendCollection(Models.JobLeavesCollection);

  /**
   * A set of modifiable/displayable filter criteria which is
   * used to create a collection. The collection's crmCriteria
   * are kept in sync with the filter criteria.
   *
   * @type {*}
   */
  Models.AbsenceCriteria = Backbone.Model.extend({
    defaults: {
      //activity_type_id: int or array(int); optional
      //activity_type_id: 3,

      // period_id: int or array(int); optional
      period_id: _.last(_.keys(CRM.absenceApp.periods)),

      target_contact_id: CRM.absenceApp.contactId,

      // What's a good upper-limit? Typical year probably has 1-20 activities,
      // so 10-year history might have 200 records. Double and add a little
      // more.
      options: {
        limit: 500
      }
    }
  });

  /**
   * A set of modifiable/displayable filter criteria which is
   * used to create a collection. The collection's crmCriteria
   * are kept in sync with the filter criteria.
   *
   * @type {*}
   */
  Models.EntitlementCriteria = Backbone.Model.extend({
    defaults: {
      contact_id: CRM.absenceApp.contactId,
      options: {
        limit: 500
      }
    }
  });

});