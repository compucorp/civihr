// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRApp.module('JobTabApp.Intro', function(Intro, HRApp, Backbone, Marionette, $, _){
  Intro.Controller = {
    showIntro: function(cid){
      var mainView = new Intro.ShowView({
        contact_id: cid
      });
      HRApp.messageRegion.show(mainView);
    }
  }
});
