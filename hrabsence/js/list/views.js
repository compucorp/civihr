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
        'absences_by_period': this.options.collection.groupBy(function(m) {return m.getPeriodId();}),
        'collection': this.options.collection,
        'absenceTypeCollection': this.options.absenceTypeCollection,
        'entitlementCollection': this.options.entitlementCollection,
        'periods': CRM.absenceApp.periods,
        'FieldOptions': {
          'activity_type_id': CRM.absenceApp.activityTypes,
          'period_id': _.reduce(CRM.absenceApp.periods, function(r,m){r[m.id]= m.title; return r;}, {}),
          'status_id': CRM.PseudoConstant.absenceStatus
        }
      };
    },
    initialize: function(options) {
      if (console.log) console.log('ListView.initialize  with ' + options.collection.models.length + ' item(s)');
      var view = this,
        jobLeavesCollection = new HRAbsenceApp.Models.JobLeavesCollection([], {
          crmCriteria: { contact_id: CRM.absenceApp.contactId, 'api.HRJobLeave.get': 1}
        });
        jobLeavesCollection.fetch({
          success: function(e) {
          var primeJob = jobLeavesCollection.getPrimaryJobID(),
            contractLeaves = jobLeavesCollection.getContractLeaves();
          _.each(CRM.absenceApp.periods, function(periodVals, periodIndex) {
            var leaveExistsForPeriod = view.options.entitlementCollection.findByPeriod(periodVals['id']);
            if (!leaveExistsForPeriod) {
              _.each(contractLeaves[primeJob], function(leave_amount, leave_type) {
                if ($.isNumeric(leave_type)) {
                  view.options.entitlementCollection.create({
                    contact_id: CRM.absenceApp.contactId,
                    period_id: periodVals['id'],
                    type_id: leave_type,
                    amount: leave_amount
                  });
                }
              });
            }
          });
        }
      });
      this.listenTo(options.collection, 'reset', this.render);
    },
    onRender: function() {
      if (console.log) console.log('ListView.onRender with ' + this.options.collection.models.length + ' item(s)');
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