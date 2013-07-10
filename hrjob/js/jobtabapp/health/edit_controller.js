CRM.HRApp.module('JobTabApp.Health', function(Health, HRApp, Backbone, Marionette, $, _){
  Health.Controller = {
    editHealth: function(cid, jobId){
      var model = HRApp.request("hrjob:entity", jobId);
      var mainView = new Health.EditView({
        model: model
      });
      HRApp.mainRegion.show(mainView);
    }
  }
});
