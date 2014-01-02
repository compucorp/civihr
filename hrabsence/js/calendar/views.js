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
        'FieldOptions': {
          'activity_type_id': CRM.absenceApp.activityTypes,
          'period_id': CRM.absenceApp.periods
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
    }
  });
});