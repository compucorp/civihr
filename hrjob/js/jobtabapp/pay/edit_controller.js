CRM.HRApp.module('JobTabApp.Pay', function(Pay, HRApp, Backbone, Marionette, $, _){
  Pay.Controller = {
    editPay: function(cid, jobId){
      var hrjobModel = HRApp.request("hrjob:entity", jobId);
      var hrjobPayModel = new HRApp.Entities.HRJobPay({
        job_id: null,
        pay_grade: 'paid',
        pay_amount: 350,
        pay_unit: 'Day'
      });
      var mainView = new Pay.EditView({
        model: hrjobPayModel,
        hrjob: hrjobModel
      });
      HRApp.mainRegion.show(mainView);
    }
  }
});
