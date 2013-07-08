CRM.HRApp.module('JobTabApp', function(JobTabApp, HRApp, Backbone, Marionette, $, _){
  JobTabApp.Router = Marionette.AppRouter.extend({
    appRoutes: {
      ":cid/hrjob" : "showIntro"
    }
  });

  var API = {
    showIntro: function(cid){
      JobTabApp.Intro.Controller.showIntro(cid);
      // ContactManager.HeaderApp.List.Controller.setActiveHeader("intro");
    }
  };

  HRApp.on("intro:show", function(cid){
    HRApp.navigate(cid + "/hrjob");
    API.showIntro(cid);
  });

  HRApp.addInitializer(function(){
    new JobTabApp.Router({
      controller: API
    });
  });
});