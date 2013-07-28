CRM.HRApp.module('JobTabApp', function(JobTabApp, HRApp, Backbone, Marionette, $, _) {

  // FIXME: allows multiple cid's but only one JobCollection
  var jobCollection = new CRM.HRApp.Entities.HRJobCollection([], {
    crmCriteria: {contact_id: CRM.jobTabApp.contact_id}
  });
  HRApp.on("initialize:after", function() {
    HRApp.JobTabApp.Tree.Controller.show(CRM.jobTabApp.contact_id, jobCollection);
  });

  JobTabApp.Router = Marionette.AppRouter.extend({
    appRoutes: {
      ":cid/hrjob": "showIntro",
      ":cid/hrjob/add": "addJob",
      ":cid/hrjob/:id": "showSummary",
      ":cid/hrjob/:id/general": "editGeneral",
      ":cid/hrjob/:id/health": "editHealth",
      ":cid/hrjob/:id/hour": "editHour",
      ":cid/hrjob/:id/leave": "editLeave",
      ":cid/hrjob/:id/pay": "editPay",
      ":cid/hrjob/:id/pension": "editPension",
      ":cid/hrjob/:id/role": "editRole"
    }
  });

  var API = {
    addJob: function(cid) {
      JobTabApp.General.Controller.addGeneral(cid, jobCollection);
    },
    showIntro: function(cid) {
      JobTabApp.Intro.Controller.showIntro(cid);
    },
    showSummary: function(cid, jobId) {
      JobTabApp.Summary.Controller.showSummary(cid, jobId);
    },
    editGeneral: function(cid, jobId) {
      JobTabApp.General.Controller.editGeneral(cid, jobId);
    },
    editHealth: function(cid, jobId) {
      JobTabApp.Health.Controller.editHealth(cid, jobId);
    },
    editHour: function(cid, jobId) {
      JobTabApp.Hour.Controller.editHour(cid, jobId);
    },
    editLeave: function(cid, jobId) {
      JobTabApp.Leave.Controller.editLeave(cid, jobId);
    },
    editPay: function(cid, jobId) {
      JobTabApp.Pay.Controller.editPay(cid, jobId);
    },
    editPension: function(cid, jobId) {
      JobTabApp.Pension.Controller.editPension(cid, jobId);
    },
    editRole: function(cid, jobId) {
      JobTabApp.Role.Controller.editRole(cid, jobId);
    }
  };

  HRApp.on("intro:show", function(cid) {
    HRApp.navigate(cid + "/hrjob");
    API.showIntro(cid);
  });

  HRApp.on("hrjob:summary:show", function(cid, jobId) {
    HRApp.navigate(cid + "/hrjob/" + jobId);
    API.showSummary(cid, jobId);
  });

  // For the moment, we'll define event listeners with this basic pattern.
  // However, it would be reasonable to break these out and define
  // each separately
  _.each({
    "general": "editGeneral",
    "health": "editHealth",
    "hour": "editHour",
    "leave": "editLeave",
    "pay": "editPay",
    "pension": "editPension",
    "role": "editRole"
  }, function(apiAction, editableModule, list) {
    HRApp.on("hrjob:" + editableModule + ":edit", function(cid, jobId) {
      HRApp.navigate(cid + "/hrjob/" + jobId + "/" + editableModule);
      API[apiAction](cid, jobId);
    });
  });

  HRApp.on("hrjob:add", function(cid) {
    HRApp.navigate(cid + "/hrjob/add");
    API.addJob(cid);
  });

  HRApp.addInitializer(function() {
    new JobTabApp.Router({
      controller: API
    });
  });
});