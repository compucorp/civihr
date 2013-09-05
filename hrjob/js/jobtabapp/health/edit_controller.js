// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRApp.module('JobTabApp.Health', function(Health, HRApp, Backbone, Marionette, $, _){
  Health.Controller = {
    editHealth: function(cid, jobId){
      HRApp.trigger('ui:block', ts('Loading'));
      CRM.Backbone.findCreate({
        CollectionClass: HRApp.Entities.HRJobHealthCollection,
        crmCriteria: {
          job_id: jobId
        },
        success: function(model) {
          HRApp.trigger('ui:unblock');
          var mainView = new Health.EditView({
            model: model
          });
          HRApp.mainRegion.show(mainView);
        },
        error: function(ignore, error) {
          HRApp.trigger('ui:unblock');
          var treeView = new HRApp.Common.Views.Failed();
          HRApp.mainRegion.show(treeView);
        }
      });
    }
  }
});
