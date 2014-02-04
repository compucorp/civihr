// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRAbsenceApp = new Marionette.Application();
CRM.HRAbsenceApp.addRegions({
  newRegion: ".hrabsence-new-region",
  filterRegion: ".hrabsence-filter-region",
  tabsRegion: ".hrabsence-tabs-region",
  contentRegion: ".hrabsence-content-region"
});

CRM.HRAbsenceApp.module('Main', function(Main, HRAbsenceApp, Backbone, Marionette, $, _) {

  // Shared state
  var absenceCriteria; // List of filter criteria
  var absenceCollection; // List of matching absences
  var entitlementCriteria; // HRAbsenceEntitlement filter criteria

  HRAbsenceApp.Router = Marionette.AppRouter.extend({
    appRoutes: {
      "hrabsence/list": "showList",
      "hrabsence/calendar": "showCalendar",
      "hrabsence/statistics": "showStatistics"
    }
  });

  var API = {
    showList: function() {
      HRAbsenceApp.contentRegion.show(CRM.av = new HRAbsenceApp.List.ListView({
        criteria: absenceCriteria,
        collection: absenceCollection,
        entitlementCollection: entitlementCollection,
        absenceTypeCollection: absenceTypeCollection
      }));
    },
    showCalendar: function() {
      HRAbsenceApp.contentRegion.show(new HRAbsenceApp.Calendar.CalendarView({
        criteria: absenceCriteria,
        collection: absenceCollection
      }));
    },
    showStatistics: function() {
      HRAbsenceApp.contentRegion.show(new HRAbsenceApp.Statistics.StatisticsView({
        criteria: absenceCriteria,
        collection: absenceCollection,
        entitlementCollection: entitlementCollection,
        absenceTypeCollection: absenceTypeCollection
      }));
    }
  };

  // e.g. listen for event "hrabsence:showList": call API.showList and set path to "/hrabsence/list"
  _.each({
    '/hrabsence/list': 'showList',
    '/hrabsence/calendar': 'showCalendar',
    '/hrabsence/statistics': 'showStatistics'
  }, function(apiAction, path) {
    HRAbsenceApp.on("hrabsence:" + apiAction, function() {
      Backbone.history.navigate(path);
      HRAbsenceApp.trigger('navigate', {
        path: path,
        event: 'hrabsence:' + apiAction
      });
      API[apiAction]();
    });
  });

  HRAbsenceApp.addInitializer(function() {
    new HRAbsenceApp.Router({
      controller: API
    });

    absenceCriteria = new HRAbsenceApp.Models.AbsenceCriteria({
      target_contact_id: CRM.absenceApp.contactId,
      options: {'absence-range': 1}
    });
    absenceCollection = new HRAbsenceApp.Models.AbsenceCollection([], {
      crmCriteriaModel: absenceCriteria,
      crmActions: {"get": "getabsences"}
    });
    entitlementCriteria = new HRAbsenceApp.Models.EntitlementCriteria({
      contact_id: CRM.absenceApp.contactId,
      options: {'absence-range': 1}
    });
    entitlementCollection = new HRAbsenceApp.Models.EntitlementCollection([], {
      crmCriteriaModel: entitlementCriteria
    });

    // NOTE: Generally don't like to put globalish variables in HRAbsenceApp, but this
    // data doesn't really change much, and it makes AbsenceModel.isDebit() much cleaner.
    absenceTypeCollection = HRAbsenceApp.absenceTypeCollection = new HRAbsenceApp.Models.AbsenceTypeCollection(_.values(CRM.absenceApp.absenceTypes));
  });

  HRAbsenceApp.on("initialize:after", function() {
    if (Backbone.history) {
      Backbone.history.start();
      if (Backbone.history.fragment === "") {
        HRAbsenceApp.trigger('hrabsence:showList');
      }
    }

    HRAbsenceApp.newRegion.show(new HRAbsenceApp.New.NewView());
    HRAbsenceApp.filterRegion.show(new HRAbsenceApp.Filter.FilterView({
      model: absenceCriteria
    }));
    HRAbsenceApp.tabsRegion.show(new HRAbsenceApp.Tabs.TabsView());

    absenceCollection.fetch({reset: true});
    entitlementCollection.fetch({reset: true});
  });

  /**
   *
   * @param string|int sec the number of seconds in the duration
   * @return {String}
   */
  HRAbsenceApp.formatDuration = function(sec) {
    return HRAbsenceApp.formatFloat(parseFloat(sec) / CRM.absenceApp.standardDay);
  };

  HRAbsenceApp.formatFloat = function(float) {
    if (float == 0) {
      return ' 0.00';
    } else if (float < 0) {
      return '' + float.toFixed(2);
    } else {
      return '+' + float.toFixed(2);
    }
  };

});
