CRM.HRApp.module('JobTabApp', function(JobTabApp, HRApp, Backbone, Marionette, $, _){
  JobTabApp.Router = Marionette.AppRouter.extend({
    appRoutes: {
      ":cid/hrjob" : "showIntro",
      ":cid/hrjob/:id" : "showSummary"
    }
  });

  var API = {
    showIntro: function(cid){
      JobTabApp.Intro.Controller.showIntro(cid);
    },
    showSummary: function(cid, jobId){
      JobTabApp.Summary.Controller.showSummary(cid, jobId);
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

  HRApp.addInitializer(function(){
    new JobTabApp.Router({
      controller: API
    });
  });
});