CRM.HRApp.module('JobTabApp.Tree', function(Tree, HRApp, Backbone, Marionette, $, _) {
  var treeView;

  Tree.Controller = {
    show: function(cid, jobCollection) {
      if (!cid) {
        throw "Missing argument: cid";
      }
      jobCollection.fetch({
        success: function() {
          treeView = new Tree.View({
            collection: jobCollection
          });
          HRApp.treeRegion.show(treeView);
          treeView.selectRoute(Backbone.history.fragment);
        },
        error: function(collection, errorData) {
          treeView = null;
          var errorView = new HRApp.Common.Views.Failed();
          HRApp.treeRegion.show(errorView);
        }
      });
    },

    /**
     * Respond to application-level navigation events
     *
     * @param string route The fragment to append to the URL
     * @param Object options
     */
    onNavigate: function(route, options) {
      if (!treeView) {
        return;
      }
      treeView.selectRoute(route);
    }
  };

  HRApp.on("navigate", Tree.Controller.onNavigate);
});
