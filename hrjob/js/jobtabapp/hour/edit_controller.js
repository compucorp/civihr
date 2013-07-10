CRM.HRApp.module('JobTabApp.Hour', function(Hour, HRApp, Backbone, Marionette, $, _){
  Hour.Controller = {
    editHour: function(cid, jobId){
      var model = HRApp.request("hrjob:entity", jobId);
      var mainView = new Hour.EditView({
        model: model
      });
      HRApp.mainRegion.show(mainView);
    }
  }
});
