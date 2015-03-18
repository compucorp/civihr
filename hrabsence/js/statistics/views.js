// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRAbsenceApp.module('Statistics', function(Statistics, HRAbsenceApp, Backbone, Marionette, $, _) {

  /**
   * A view which computes statistics about one's absences by period, type,
   * and status.
   *
   * Constructor arguments:
   *  - collection: AbsenceCollection
   *  - criteria: AbsenceCriteria
   *
   * This view is currently based on ItemView because it's just a placeholder.
   * For the final/real implementation, one might use ItemView, CompositeView,
   * CollectionView, or something else.
   *
   * @type {*}
   */
  Statistics.StatisticsView = Marionette.ItemView.extend({
    template: '#hrabsence-statistics-template',
    templateHelpers: function() {
      return {
        'stats': this.createStatistics(),
        'FieldOptions': {
          'activity_type_id': CRM.absenceApp.activityTypes,
          'period_id': _.reduce(CRM.absenceApp.periods, function(r,m){r[m.id]= m.title; return r;}, {})
        }
      };
    },
    initialize: function(options) {
      if (console.log) console.log('StatisticsView.initialize  with ' + options.collection.models.length + ' item(s)');
      this.listenTo(options.collection, 'reset', this.render);
    },
    onRender: function() {
      if (console.log) console.log('StatisticsView.onRender with ' + this.options.collection.models.length + ' item(s)');
      this.$('.activity-count').text(this.options.collection.models.length);
    },

    /** @return array of statistics */
    createStatistics: function() {
      var stats = {};
      var selectedPeriods = this.options.criteria.get('period_id');
      var selectedAbsenceType = this.options.criteria.get('activity_type_id');
      var absencesTypes = this.options.absenceTypeCollection.getAbsenceTypes();

      this.options.entitlementCollection.each(function(model) {
        var activity_type_id = '';
        _.each(absencesTypes, function(absenceTypeID, activityTypeID) {
          if (absenceTypeID == model.get('type_id')) {
            var activity = activityTypeID;
            activity_type_id = activityTypeID;
            var statsKey = model.get('period_id') + '-' + activity_type_id;
            if ((_.contains(selectedPeriods, model.get('period_id')) || !selectedPeriods) &&
              (_.contains(selectedAbsenceType, activity_type_id) || !selectedAbsenceType)) {
              if (!stats[statsKey]) {
                stats[statsKey] = {
                  period_id: model.get('period_id'),
                  activity_type_id: activity_type_id,
                  entitlement: model.get('amount'),
                  requested: 0,
                  approved: 0,
                  balance: model.get('amount')
                };
              }
            }
          }
        });
      });

      this.options.collection.each(function(model) {
        var statsKey = model.getPeriodId() + '-' + model.get('activity_type_id');
        if (!stats[statsKey]) {
          stats[statsKey] = {
            period_id: model.getPeriodId(),
            activity_type_id: model.get('activity_type_id'),
            entitlement: 0,
            requested: 0,
            approved: 0,
            balance: 0
          };
        }
        if (model.get('status_id') == 2) {
          stats[statsKey].approved = parseInt(stats[statsKey].approved) + parseInt(model.get('absence_range').approved_duration);
        } else if (model.get('status_id') == 1) {
          stats[statsKey].requested = parseInt(stats[statsKey].requested) + parseInt(model.get('absence_range').duration);
        }
      });

      _.each(stats, function(stats, statsKey) {
        stats.approved = CRM.HRAbsenceApp.formatDuration(stats.approved) * CRM.HRAbsenceApp.absenceTypeCollection.findDirection(stats.activity_type_id);
        stats.requested = CRM.HRAbsenceApp.formatDuration(stats.requested) * CRM.HRAbsenceApp.absenceTypeCollection.findDirection(stats.activity_type_id);
        if (CRM.HRAbsenceApp.absenceTypeCollection.findDirection(stats.activity_type_id) == -1) {
          stats.balance = (parseFloat(stats.entitlement) + (parseFloat(stats.requested) + parseFloat(stats.approved)));
        } else {
          stats.entitlement = 0;
          stats.balance = (parseFloat(stats.requested) + parseFloat(stats.approved)) * 1;
        }
      });
      return stats;
    }
  });
});