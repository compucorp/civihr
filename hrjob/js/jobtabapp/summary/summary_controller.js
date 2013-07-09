CRM.HRApp.module('JobTabApp.Summary', function(Summary, HRApp, Backbone, Marionette, $, _){
  Summary.Controller = {
    showSummary: function(cid, jobId){
      var model = HRApp.request("hrjob:entity", jobId);
      var mainView = new Summary.ShowView({
        model: model
      });
      HRApp.mainRegion.show(mainView);
    }
  }
});
