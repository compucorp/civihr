CRM.HRApp.module('JobTabApp.Pension', function(Pension, HRApp, Backbone, Marionette, $, _){
  Pension.Controller = {
    editPension: function(cid, jobId){
      var model = HRApp.request("hrjob:entity", jobId);
      var mainView = new Pension.EditView({
        model: model
      });
      HRApp.mainRegion.show(mainView);
    }
  }
});
