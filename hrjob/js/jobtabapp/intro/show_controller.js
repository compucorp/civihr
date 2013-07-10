CRM.HRApp.module('JobTabApp.Intro', function(Intro, HRApp, Backbone, Marionette, $, _){
  Intro.Controller = {
    showIntro: function(cid){
      var mainView = new Intro.ShowView();
      HRApp.mainRegion.show(mainView);
    }
  }
});
