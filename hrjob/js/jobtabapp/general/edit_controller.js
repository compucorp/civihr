CRM.HRApp.module('JobTabApp.General', function(General, HRApp, Backbone, Marionette, $, _){
  General.Controller = {
    editGeneral: function(cid, jobId){
      var model = HRApp.request("hrjob:entity", jobId);
      var mainView = new General.EditView({
        model: model
      });
      HRApp.mainRegion.show(mainView);
    }
  }
});
