// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRAbsenceApp.module('List', function(List, HRAbsenceApp, Backbone, Marionette, $, _) {

  /**
   * A view which lists out absence-request activities and displays
   * various totals.
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
  List.ListView = Marionette.ItemView.extend({
    template: '#hrabsence-list-template',
    templateHelpers: function() {
      return {
        'active_activity_types': this.options.collection.findActiveActivityTypes(),
        'collection': this.options.collection,
        'absences_by_period': this.options.collection.groupBy(function(m) {return m.getPeriodId();}),
        'periods': CRM.absenceApp.periods,
        'FieldOptions': {
          'activity_type_id': CRM.absenceApp.activityTypes,
          'period_id': _.reduce(CRM.absenceApp.periods, function(r,m){r[m.id]= m.title; return r;}, {}),
          'status_id': CRM.PseudoConstant.activityStatus
        }
      };
    },
    initialize: function(options) {
      if (console.log) console.log('ListView.initialize  with ' + options.collection.models.length + ' item(s)');
      this.listenTo(options.collection, 'reset', this.render);
    },
    onRender: function() {
      if (console.log) console.log('ListView.onRender with ' + this.options.collection.models.length + ' item(s)');
      this.$('.activity-count').text(this.options.collection.models.length);
    }
  });
});