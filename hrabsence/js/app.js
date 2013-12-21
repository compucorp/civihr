// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRAbsenceApp = new Marionette.Application();
CRM.HRAbsenceApp.addRegions({
  newRegion: ".hrabsence-new-region",
  filterRegion: ".hrabsence-filter-region",
  tabsRegion: ".hrabsence-tabs-region"
});

CRM.HRAbsenceApp.module('Main', function(Main, HRAbsenceApp, Backbone, Marionette, $, _) {

  // Shared state
  var absenceCriteria; // List of filter criteria
  var absenceCollection; // List of matching absences

  HRAbsenceApp.Router = Marionette.AppRouter.extend({
    appRoutes: {
      "hrabsence/list": "showList",
      "hrabsence/calendar": "showCalendar",
      "hrabsence/statistics": "showStatistics"
    }
  });

  var API = {
    showList: function() {
      console.log('todo: showList', absenceCollection);
    },
    showCalendar: function() {
      console.log('todo: showCalendar', absenceCollection);
    },
    showStatistics: function() {
      console.log('todo: showStatistics', absenceCollection);
    }
  };

  // e.g. listen for event "hrabsence:showList": call API.showList and set path to "/hrabsence/list"
  _.each({
    '/hrabsence/list': 'showList',
    '/hrabsence/calendar': 'showCalendar',
    '/hrabsence/statistics': 'showStatistics'
  }, function(apiAction, path) {
    HRAbsenceApp.on("hrabsence:" + apiAction, function(cid, jobId) {
      Backbone.history.navigate(path, {
        success: function() {
          API[apiAction](cid, jobId);
        }
      });
    });
  });

  HRAbsenceApp.addInitializer(function() {
    new HRAbsenceApp.Router({
      controller: API
    });

    HRAbsenceApp.newRegion.show(new HRAbsenceApp.New.NewView());
    HRAbsenceApp.filterRegion.show(new HRAbsenceApp.Filter.FilterView({
      model: absenceCriteria
    }));
    HRAbsenceApp.tabsRegion.show(new HRAbsenceApp.Tabs.TabsView());

    absenceCriteria = new HRAbsenceApp.Models.AbsenceCriteria();
    absenceCollection = new HRAbsenceApp.Models.AbsenceCollection([], {
      crmCriteriaModel: absenceCriteria
    });
    //absenceCriteria.updateCollection();
  });

  HRAbsenceApp.on("initialize:after", function() {
    if (Backbone.history) {
      Backbone.history.start();
      if (Backbone.history.fragment === "") {
        HRAbsenceApp.trigger('hrabsence:showList');
      }
    }

    absenceCollection.listenTo(absenceCollection, 'reset', function() {
      console.log('reset collection', arguments);
    });

    // absenceCriteria.trigger('change');
    absenceCriteria.set('foo', 'bang');
  });
});