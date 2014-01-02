// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRAbsenceApp.module('Models', function(Models, HRAbsenceApp, Backbone, Marionette, $, _) {
  Models.Absence = Backbone.Model.extend({
  });
  CRM.Backbone.extendModel(Models.Absence, 'Activity');

  Models.AbsenceCollection = Backbone.Collection.extend({
    model: Models.Absence
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
      //TODO: activity_type_id: ['IN', _.keys(CRM.absenceApp.activityTypes)]
      activity_type_id: 3,

      // TODO: period_id: ['IN', [period_id: _.last(_.keys(CRM.absenceApp.periods))]]
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