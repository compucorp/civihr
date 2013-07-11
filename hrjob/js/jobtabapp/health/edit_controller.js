CRM.HRApp.module('JobTabApp.Health', function(Health, HRApp, Backbone, Marionette, $, _){
  Health.Controller = {
    editHealth: function(cid, jobId){
      var hrjobModel = HRApp.request("hrjob:entity", jobId);
      var hrjobHealthModel = new HRApp.Entities.HRJobHealth({
        job_id: null,
        provider: 'Unknown',
        plan_type: 'Family',
        description: 'some description',
        dependents: 'Jane and John'
      });
      var mainView = new Health.EditView({
        model: hrjobHealthModel,
        hrjob: hrjobModel
      });
      HRApp.mainRegion.show(mainView);
    }
  }
});
