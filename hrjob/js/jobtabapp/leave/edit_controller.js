// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
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
