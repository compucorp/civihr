// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRApp.module('JobTabApp.Pay', function(Pay, HRApp, Backbone, Marionette, $, _) {
  Pay.Controller = {
    editPay: function(cid, jobId) {
      HRApp.trigger('ui:block', ts('Loading'));
      CRM.Backbone.findCreate({
        CollectionClass: HRApp.Entities.HRJobPayCollection,
        crmCriteria: {
          job_id: jobId
        },
        success: function(model) {

          var settingsModel = new HRApp.Entities.Setting({
            id: CRM.jobTabApp.domain_id
          });
          settingsModel.fetch({
            success: function() {
              HRApp.trigger('ui:unblock');
              var mainView = new Pay.EditView({
                settings: settingsModel,
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
