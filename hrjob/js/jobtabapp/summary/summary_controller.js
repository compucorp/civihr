CRM.HRApp.module('JobTabApp.Summary', function(Summary, HRApp, Backbone, Marionette, $, _){
  Summary.Controller = {
    showSummary: function(cid, jobId){
      var model = new HRApp.Entities.HRJob({id: jobId});
      model.fetch({
        success: function() {
          var mainView = new Summary.ShowView({
            model: model
          });
          HRApp.mainRegion.show(mainView);
        },
        error: function() {
          var treeView = new HRApp.Common.Views.Failed();
          HRApp.mainRegion.show(treeView);
        }
      });
    }
  }
});
