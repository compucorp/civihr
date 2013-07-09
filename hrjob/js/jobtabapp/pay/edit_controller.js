CRM.HRApp.module('JobTabApp.Pay', function(Pay, HRApp, Backbone, Marionette, $, _){
  Pay.Controller = {
    editPay: function(cid, jobId){
      var model = HRApp.request("hrjob:entity", jobId);
      var mainView = new Pay.EditView({
        model: model
      });
      HRApp.mainRegion.show(mainView);
    }
  }
});
