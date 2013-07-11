CRM.HRApp.module('JobTabApp.Pension', function(Pension, HRApp, Backbone, Marionette, $, _){
  Pension.Controller = {
    editPension: function(cid, jobId){
      var hrjobModel = HRApp.request("hrjob:entity", jobId);
      var hrjobPensionModel = new HRApp.Entities.HRJobPension({
        job_id: null,
        is_enrolled: 1,
        contrib_pct: 33.3
      });
      var mainView = new Pension.EditView({
        model: hrjobPensionModel,
        hrjob: hrjobModel
      });
      HRApp.mainRegion.show(mainView);
    }
  }
});
