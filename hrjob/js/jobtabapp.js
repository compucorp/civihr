CRM.HRApp.module('JobTabApp', function(JobTabApp, HRApp, Backbone, Marionette, $, _){
  JobTabApp.Router = Marionette.AppRouter.extend({
    appRoutes: {
      ":cid/hrjob" : "showIntro",
      ":cid/hrjob/:id" : "showSummary",
      ":cid/hrjob/:id/general": "editGeneral",
      ":cid/hrjob/:id/role": "editRole"
    }
  });

  var API = {
    showIntro: function(cid){
      JobTabApp.Intro.Controller.showIntro(cid);
    },
    showSummary: function(cid, jobId) {
      JobTabApp.Summary.Controller.showSummary(cid, jobId);
    },
    editGeneral: function(cid, jobId) {
      JobTabApp.General.Controller.editGeneral(cid, jobId);
    },
    editRole: function(cid, jobId) {
      JobTabApp.Role.Controller.editRole(cid, jobId);
    }
  };

  HRApp.on("intro:show", function(cid){
    HRApp.navigate(cid + "/hrjob");
    API.showIntro(cid);
  });

  HRApp.on("hrjob:summary:show", function(cid, jobId){
    HRApp.navigate(cid + "/hrjob/" + jobId);
    API.showSummary(cid, jobId);
  });

  // For the moment, we'll define event listeners with this basic pattern.
  // However, it would be reasonable to break these out and define
  // each separately
  _.each({
    "general": "editGeneral",
    "role": "editRole"
  }, function(apiAction, editableModule, list){
    HRApp.on("hrjob:"+editableModule+":edit", function(cid, jobId){
      HRApp.navigate(cid + "/hrjob/" + jobId + "/" + editableModule);
      API[apiAction](cid, jobId);
    });
  });


  HRApp.addInitializer(function(){
    new JobTabApp.Router({
      controller: API
    });
  });
});