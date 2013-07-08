CRM.HRApp.module('JobTabApp', function(JobTabApp, HRApp, Backbone, Marionette, $, _){
  JobTabApp.Router = Marionette.AppRouter.extend({
    appRoutes: {
      "hrjob" : "showIntro"
    }
  });

  var API = {
    showIntro: function(){
      JobTabApp.Intro.Controller.showIntro();
      // ContactManager.HeaderApp.List.Controller.setActiveHeader("intro");
    }
  };

  HRApp.on("intro:show", function(){
    HRApp.navigate("hrjob");
    API.showIntro();
  });

  HRApp.addInitializer(function(){
    new JobTabApp.Router({
      controller: API
    });
  });
});