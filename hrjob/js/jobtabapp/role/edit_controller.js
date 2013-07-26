CRM.HRApp.module('JobTabApp.Role', function(Role, HRApp, Backbone, Marionette, $, _){
  Role.Controller = {
    editRole: function(cid, jobId){
      HRApp.trigger('ui:block', ts('Loading'));
      var roleCollection = new CRM.HRApp.Entities.HRJobRoleCollection([], {
        crmCriteria: {
          job_id: jobId
        }
      });
      roleCollection.fetch({
        success: function() {
          HRApp.trigger('ui:unblock');
          var mainView = new Role.TableView({
            newModelDefaults: {
              job_id: jobId,
              title: ts('New Role')
            },
            collection: roleCollection
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
