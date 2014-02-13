// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRAbsenceApp.module('Calendar', function(Calendar, HRAbsenceApp, Backbone, Marionette, $, _) {

  /**
   * A view which visualizes absence-request activities by month and
   * day.
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
  Calendar.CalendarView = Marionette.ItemView.extend({
    template: '#hrabsence-calendar-template',
    templateHelpers: function() {
      return {
        'active_period_ids': (this.options.criteria && this.options.criteria.get('period_id')) ? this.options.criteria.get('period_id') : _.keys(CRM.absenceApp.periods),
        'collection': this.options.collection,
        'periods': CRM.absenceApp.periods,
        'activity_by_date': this.collection.createDateIndex(),
        'month_stats': this.collection.createMonthStats(),
        'FieldOptions': {
          'activity_type_id': CRM.absenceApp.activityTypes,
          'period_id': _.reduce(CRM.absenceApp.periods, function(r,m){r[m.id]= m.title; return r;}, {})
        }
      };
    },
    initialize: function(options) {
      if (console.log) console.log('CalendarView.initialize  with ' + options.collection.models.length + ' item(s)');
      this.listenTo(options.collection, 'reset', this.render);
    },
    onRender: function() {
      if (console.log) console.log('CalendarView.onRender with ' + this.options.collection.models.length + ' item(s)');
      this.$('.activity-count').text(this.options.collection.models.length);
      this.$('.hrabsence-open').each(function(){
        var url = CRM.url('civicrm/absence/set', {
          reset: 1,
          action: 'update',
          aid: $(this).attr('data-activity')
        });
        $(this).attr('href', url);
      });
    }
  });
});