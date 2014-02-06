// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRAbsenceApp.module('Entitlements', function(Entitlements, HRAbsenceApp, Backbone, Marionette, $, _) {

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
  Entitlements.EntitlementsView = Marionette.ItemView.extend({
    template: '#hrabsence-entitlements-template',
    templateHelpers: function() {
      return {
        'collection': this.options.collection,
        'entitlements': this.options.entitlementCollection.getEntitlements(),
        'absencetype': this.options.absenceTypeCollection.getAbsenceTypes(),
        'FieldOptions': {
          'activity_type_id': CRM.absenceApp.activityTypes,
          'period_id': _.reduce(CRM.absenceApp.periods, function(r,m){r[m.id]= m.title; return r;}, {})
        }
      };
    },
    initialize: function(options) {
      if (console.log) console.log('EntitlementsView.initialize  with ' + options.entitlementCollection.models.length + ' item(s)');
      this.listenTo(options.collection, 'reset', this.render);
    },
    onRender: function() {
      if (console.log) console.log('EntitlementsView.onRender with ' + this.options.entitlementCollection.models.length + ' item(s)');
    }
  });
});