CRM.HRApp.module('JobTabApp.Tree', function(Tree, HRApp, Backbone, Marionette, $, _) {
  Tree.Controller = {
    show: function(cid, jobCollection) {
      if (!cid) {
        throw "Missing argument: cid";
      }
      jobCollection.fetch({
        success: function() {
          var treeView = new Tree.View({
            collection: jobCollection
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
