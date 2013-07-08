CRM.HRApp.module('JobTabApp.Intro', function(Intro, HRApp, Backbone, Marionette, $, _){
  Intro.Controller = {
    showIntro: function(){
      var exampleMainView = new Intro.ShowView();
      HRApp.mainRegion.show(exampleMainView);
    }
  }
});
