CRM.HRApp.module('JobTabApp.Hour', function(Hour, HRApp, Backbone, Marionette, $, _){
  Hour.Controller = {
    editHour: function(cid, jobId){
      HRApp.trigger('ui:block', ts('Loading'));
      CRM.Backbone.findCreate({
        CollectionClass: HRApp.Entities.HRJobHourCollection,
        crmCriteria: {
          job_id: jobId
        },
/*
        defaults: {
          hours_type: '',
          hours_amount: '',
          hours_unit: '',
          hours_fte: ''
        },
*/
        success: function(model) {
          HRApp.trigger('ui:unblock');
          var mainView = new Hour.EditView({
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
