CRM.HRApp.module('JobTabApp.Pay', function(Pay, HRApp, Backbone, Marionette, $, _){
  Pay.Controller = {
    editPay: function(cid, jobId){
      HRApp.trigger('ui:block', ts('Loading'));
      CRM.Backbone.findCreate({
        CollectionClass: HRApp.Entities.HRJobPayCollection,
        crmCriteria: {
          job_id: jobId
        },
/*
        defaults: {
          pay_grade: '',
          pay_amount: '',
          pay_unit: ''
        },
*/
        success: function(model) {
          HRApp.trigger('ui:unblock');
          var mainView = new Pay.EditView({
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
