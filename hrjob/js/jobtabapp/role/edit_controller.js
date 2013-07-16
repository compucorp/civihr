CRM.HRApp.module('JobTabApp.Role', function(Role, HRApp, Backbone, Marionette, $, _){
  Role.Controller = {
    editRole: function(cid, jobId){
      var model = HRApp.request("hrjob:entity", jobId);
      var roleCollection = new CRM.HRApp.Entities.HRJobRoleCollection([
        {title: 'Assistant Break Dancer', hours: 10, description: 'Keeps all the other break dancers in line by busting an occasional move when someone breaks out of line', department: null},
        {title: 'Choreographic research', hours: 5, description: '', department: null, functional_area: 'Choreography', location: 'Home'},
        {title: 'Dental insurance coding', hours: 3, description: '',  department: 'Accounting'}
      ]);
      var mainView = new Role.TableView({
        collection: roleCollection,
        model: model
      });
      HRApp.mainRegion.show(mainView);
    }
  }
});
