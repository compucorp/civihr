CRM.HRApp.module('JobTabApp.Intro', function(Intro, HRApp, Backbone, Marionette, $, _){
  Intro.ShowView = Marionette.ItemView.extend({
    template: '#hrjob-intro-template'
  });
});
