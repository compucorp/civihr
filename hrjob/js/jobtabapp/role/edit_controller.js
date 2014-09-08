// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRApp.module('JobTabApp.Role', function(Role, HRApp, Backbone, Marionette, $, _){
  Role.Controller = {
    editRole: function(cid, jobId){
      HRApp.trigger('ui:block', ts('Loading'));
      var roleCollection = new CRM.HRApp.Entities.HRJobRoleCollection([], {
        crmCriteria: {
          job_id: jobId
        }
      });
      var hourCollection = new CRM.HRApp.Entities.HRJobHourCollection([], {
        crmCriteria: {contact_id: cid, job_id: jobId},
      });
      roleCollection.fetch({
        success: function() {
          HRApp.trigger('ui:unblock');
            var payTotal = 0,
            hourUnit = null,hoursType = null,
            hourAmount = 0;
          hourCollection.fetch({
            success: function() {
	      if (hourCollection.first()) {
                hourUnit = hourCollection.first().get("hours_unit");
                hourAmount = hourCollection.first().get("hours_amount");
                hoursType = hourCollection.first().get("hours_type");
	      }
	      _.forEach(roleCollection.models, function (model) {
	        payTotal += parseInt(model.get('percent_pay_role'));
	      });
              var mainView = new Role.TableView({
                newModelDefaults: {
                  job_id: jobId,
                  title: ts("New Role"),
                  percent_pay_role: 100 - parseInt(payTotal)
                },
                collection: roleCollection,
                hourInfo: {
                  hourUnit: hourUnit,
                  hourAmount: hourAmount,
                  hoursType: hoursType
	        }
              });
              HRApp.mainRegion.show(mainView);
            },
          });
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
