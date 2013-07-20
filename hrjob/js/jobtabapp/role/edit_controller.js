CRM.HRApp.module('JobTabApp.Role', function(Role, HRApp, Backbone, Marionette, $, _){
  Role.Controller = {
    editRole: function(cid, jobId){
      HRApp.trigger('ui:block', ts('Loading'));
      var model = new HRApp.Entities.HRJob({id: jobId});
      var roleCollection = new CRM.HRApp.Entities.HRJobRoleCollection([
        {title: 'Assistant Break Dancer', hours: 10, description: 'Keeps all the other break dancers in line by busting an occasional move when someone breaks out of line', department: null},
        {title: 'Choreographic research', hours: 5, description: '', department: null, functional_area: 'Choreography', location: 'Home'},
        {title: 'Dental insurance coding', hours: 3, description: '',  department: 'Accounting'}
      ]);

      model.fetch({
        success: function() {
          HRApp.trigger('ui:unblock');
          var mainView = new Role.TableView({
            collection: roleCollection,
            model: model
          });
          HRApp.mainRegion.show(mainView);
        },
        error: function() {
          HRApp.trigger('ui:unblock');
          var treeView = new HRApp.Common.Views.Failed();
          HRApp.mainRegion.show(treeView);
        }
      });
    }

  }
});
