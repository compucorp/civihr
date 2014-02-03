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
      HRAbsenceApp.contentRegion.show(new HRAbsenceApp.List.ListView({
        criteria: absenceCriteria,
        collection: absenceCollection
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
        absencetypeCollection: absencetypeCollection
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
        event: 'hrabsence:'+apiAction
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
    entitlementCriteria  = new HRAbsenceApp.Models.EntitlementCriteria({
      contact_id: CRM.absenceApp.contactId,
      options: {'absence-range': 1}
    });
    entitlementCollection = new HRAbsenceApp.Models.EntitlementCollection([], {
	crmCriteriaModel: entitlementCriteria
    });
    absencetypeCollection = new HRAbsenceApp.Models.AbsenceTypeCollection([], {
    });
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
    absencetypeCollection.fetch({reset: true});
  });
});
