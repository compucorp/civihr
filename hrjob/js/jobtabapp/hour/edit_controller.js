CRM.HRApp.module('JobTabApp.Hour', function(Hour, HRApp, Backbone, Marionette, $, _){
  Hour.Controller = {
    editHour: function(cid, jobId){
      var hrjobModel = HRApp.request("hrjob:entity", jobId);
      var hrjobHourModel = new HRApp.Entities.HRJobHour({
        job_id: null,
        hours_type: 'full',
        hours_amount: 40,
        hours_unit: 'Week',
        hours_fte: 1.0
      });
      var mainView = new Hour.EditView({
        model: hrjobHourModel,
        hrjob: hrjobModel
      });
      HRApp.mainRegion.show(mainView);
    }
  }
});
