CRM.HRApp.module('JobTabApp.Role', function(Role, HRApp, Backbone, Marionette, $, _){
  Role.Controller = {
    editRole: function(cid, jobId){
      var model = HRApp.request("hrjob:entity", jobId);
      var mainView = new Role.EditView({
        model: model
      });
      HRApp.mainRegion.show(mainView);
    }
  }
});
