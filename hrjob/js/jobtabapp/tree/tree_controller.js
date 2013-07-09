CRM.HRApp.module('JobTabApp.Tree', function(Tree, HRApp, Backbone, Marionette, $, _){
  Tree.Controller = {
    show: function(cid){
      var exampleView = new Tree.View();
      HRApp.treeRegion.show(exampleView);
    }
  }
});
