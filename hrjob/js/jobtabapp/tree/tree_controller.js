CRM.HRApp.module('JobTabApp.Tree', function(Tree, HRApp, Backbone, Marionette, $, _) {
  Tree.Controller = {
    show: function(cid) {
      if (!cid) {
        throw "Missing argument: cid";
      }
      var jobs = new CRM.HRApp.Entities.HRJobCollection([], {
        crmCriteria: {contact_id: cid}
      });
      jobs.fetch({
        success: function() {
          var treeView = new Tree.View({
            collection: jobs
          });
          HRApp.treeRegion.show(treeView);
        },
        error: function(collection, errorData) {
          var treeView = new HRApp.Common.Views.Failed();
          HRApp.treeRegion.show(treeView);
        }
      });
    }
  }
});
