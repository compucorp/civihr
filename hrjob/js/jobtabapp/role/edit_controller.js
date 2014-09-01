// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRApp.module('JobTabApp.Role', function(Role, HRApp, Backbone, Marionette, $, _){
  Role.Controller = {
    editRole: function(cid, jobId){
      HRApp.trigger('ui:block', ts('Loading'));
      var jobCollection = new CRM.HRApp.Entities.HRJobCollection([], {
        crmCriteria: {contact_id: cid, job_id: jobId},
      });
      jobCollection.fetch({reset: true});
      var roleCollection = new CRM.HRApp.Entities.HRJobRoleCollection([], {
        crmCriteria: {
          job_id: jobId
        }
      });
      var hourCollection = new CRM.HRApp.Entities.HRJobHourCollection([], {
        crmCriteria: {contact_id: cid, job_id: jobId},
      });
      hourCollection.fetch({reset: true});

      roleCollection.fetch({
        success: function() {
          HRApp.trigger('ui:unblock');
            var job = jobCollection.first(), payS = 0,
            hourUnit = null,
            hourAmount = null;
	  if (hourCollection.first()) {
            hourUnit = hourCollection.first().get("hours_unit");
            hourAmount = hourCollection.first().get("hours_amount");
	  }

          var mainView = new Role.TableView({
            newModelDefaults: {
              job_id: jobId,
              title: job.get("position"),
              location: job.get("location"),
              hours: hourAmount,
              role_hours_unit: hourUnit,
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
