CRM.HRApp.module('JobTabApp', function(JobTabApp, HRApp, Backbone, Marionette, $, _) {

  // FIXME: allows multiple cid's but only one JobCollection
  var jobCollection = new CRM.HRApp.Entities.HRJobCollection([], {
    crmCriteria: {contact_id: CRM.jobTabApp.contact_id}
  });
  HRApp.on("initialize:after", function() {
    jobCollection.fetch({
      success: function() {
        HRApp.JobTabApp.Tree.Controller.show(CRM.jobTabApp.contact_id, jobCollection);
        if (CRM.HRApp.Common.Navigation.getCurrentRoute() === "") {
          if (jobCollection.isEmpty()) {
            // Stay on the default unrouted page (no #cid/hrjob in URL) but display an error.
            JobTabApp.Intro.Controller.showIntro(CRM.jobTabApp.contact_id);
          } else {
            // Redirect to the edit screen for the first job
            var job = jobCollection.first();
            CRM.HRApp.trigger('hrjob:general:edit', job.get('contact_id'), job.get('id'));
          }
        }
      },
      error: function(collection, errorData) {
        var errorView = new HRApp.Common.Views.Failed();
        HRApp.treeRegion.show(errorView);
      }
    });
  });

  JobTabApp.Router = Marionette.AppRouter.extend({
    appRoutes: {
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

  HRApp.on("hrjob:summary:show", function(cid, jobId) {
    HRApp.Common.Navigation.navigate(cid + "/hrjob/" + jobId, {
      success: function() {
        API.showSummary(cid, jobId);
      }
    });
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
      HRApp.Common.Navigation.navigate(cid + "/hrjob/" + jobId + "/" + editableModule, {
        success: function() {
          API[apiAction](cid, jobId);
        }
      });
    });
  });

  HRApp.on("hrjob:add", function(cid) {
    HRApp.Common.Navigation.navigate(cid + "/hrjob/add", {
      success: function() {
        API.addJob(cid);
      }
    });
  });

  HRApp.addInitializer(function() {
    new JobTabApp.Router({
      controller: API
    });
  });
});