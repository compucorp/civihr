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
        'periods':CRM.absenceApp.periods,
        'collection': this.options.collection,
        'entitlements': this.options.entitlementCollection.getEntitlements(),
        'absencetype': this.options.absenceTypeCollection.getAbsenceTypes(),
        'contractEntitlements': this.options.jobLeavesCollection.getContractLeaves(),
        'selectedAbsences': this.options.criteria.get('activity_type_id') ?
                              _.reduce(this.options.criteria.get('activity_type_id'), function(r,m){r[m]= m; return r;}, {})
                              : CRM.absenceApp.activityTypes,
        'selectedPeriod': this.options.criteria.get('period_id') ?
                            this.options.criteria.get('period_id')
                            : _.reduce(CRM.absenceApp.periods, function(r,m){r[m.id]= m.id; return r;}, {}),
        'FieldOptions': {
          'activity_type_id': CRM.absenceApp.activityTypes,
          'period_id': _.reduce(CRM.absenceApp.periods, function(r,m){r[m.id]= m.title; return r;}, {})
        }
      };
    },
    initialize: function(options) {
      this.listenTo(options.collection, 'reset', this.render);
    },
    onRender: function() {
      var view = this;
      this.options.entitlementCollection.each(function(entitlement){
        view.$('.hrabsence-annualentitlement-input')
          .filter('[data-period-id='+entitlement.get('period_id')+']')
          .filter('[data-absence-type-id='+entitlement.get('type_id')+']')
          .val(entitlement.get('amount'));
      });
     if (!CRM.Permissions.enableEntitlement) {
       view.$(".hrabsence-annualentitlement-input").attr('disabled','disabled');
     }
    },
    events: {
      "click .hrabsence-annualentitlement-input": function(event) {
        $(event.currentTarget)
          .removeClass('crm-editable-enabled')
          .prop('readonly', false);
      },
      "keyup .hrabsence-annualentitlement-input": function(event) {
        if (event.keyCode === 13) {
          $(event.currentTarget).blur().change();
        }
      },
      "change .hrabsence-annualentitlement-input": function(event) {
        var periodId = $(event.currentTarget).attr('data-period-id');
        var typeId = $(event.currentTarget).attr('data-absence-type-id');
        var amount = $(event.currentTarget).val();

        //validate if amount is numeric
        if(amount && !($.isNumeric(amount))) {
          $(event.currentTarget).crmError(ts("Enter numeric value for entitlement amount"));
          return;
        }
        $(event.currentTarget)
          .addClass('crm-editable-enabled')
          .prop('readonly', true);
        var entitlements = this.options.entitlementCollection.find(function(e){
          return e.get('period_id') == periodId && e.get('type_id') == typeId;
        });
        if (entitlements && amount) {
          entitlements.save({amount: amount});
        } else if(entitlements) {
          entitlements.destroy();
        } else {
          entitlement = this.options.entitlementCollection.create({
            contact_id: CRM.absenceApp.contactId,
            period_id: periodId,
            type_id: typeId,
            amount: amount
          });
        }
      }
    }
  });
});
