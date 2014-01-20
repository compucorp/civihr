// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRAbsenceApp.module('Models', function(Models, HRAbsenceApp, Backbone, Marionette, $, _) {
  Models.Absence = Backbone.Model.extend({
    initialize: function(options) {
      this.listenTo(this, 'change:activity_date_time', this.calculatePeriodId);
    },
    isInPeriod: function(period) {
      var actdate = CRM.HRAbsenceApp.moment(this.get('activity_date_time'));
      if (actdate.isBefore(CRM.HRAbsenceApp.moment(period.start_date), 'day')) return false;
      if (actdate.isAfter(CRM.HRAbsenceApp.moment(period.end_date), 'day')) return false;
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
      if (this.get('duration')) {
        // FIXME: if activity_type_id is credit, +; if debit, -
        return '+/- ' + (this.get('duration') / CRM.absenceApp.standardDay).toFixed(2);
      } else {
        return '';
      }
    }
  });
  CRM.Backbone.extendModel(Models.Absence, 'Activity');

  Models.AbsenceCollection = Backbone.Collection.extend({
    model: Models.Absence,

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
});