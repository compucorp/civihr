CRM.HRApp.module('JobTabApp.Leave', function(Leave, HRApp, Backbone, Marionette, $, _) {
  Leave.Controller = {
    editLeave: function(cid, jobId) {
      HRApp.trigger('ui:block', ts('Loading'));
      var leaveCollection = new HRApp.Entities.HRJobLeaveCollection([], {
        crmCriteria: {
          job_id: jobId
        }
      });
      leaveCollection.fetch({
        success: function() {
          HRApp.trigger('ui:unblock');
          /*
          leaveCollection.addMissingTypes(
            _.keys(CRM.FieldOptions.HRJobLeave.leave_type),
            {
              job_id: jobId,
              leave_amount: 0
            }
          );
          //*/
          //leaveCollection.sortBy('leave_type');
          var mainView = new Leave.TableView({
            collection: leaveCollection
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
