CRM.HRApp.module('JobTabApp.Leave', function(Leave, HRApp, Backbone, Marionette, $, _){
  Leave.Controller = {
    editLeave: function(cid, jobId){
      var model = HRApp.request("hrjob:entity", jobId);
      var mainView = new Leave.EditView({
        model: model
      });
      HRApp.mainRegion.show(mainView);
    }
  }
});
