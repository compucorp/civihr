// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRApp.module('JobTabApp.Tree', function(Tree, HRApp, Backbone, Marionette, $, _) {
  var treeView;

  Tree.Controller = {
    show: function(cid, jobCollection) {
      treeView = new Tree.View({
        collection: jobCollection
      });
      HRApp.treeRegion.show(treeView);
//      treeView.selectRoute(CRM.HRApp.Common.Navigation.getCurrentRoute());
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
