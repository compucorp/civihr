CRM.HRApp.module('JobTabApp.Tree', function(Tree, HRApp, Backbone, Marionette, $, _){
  Tree.View = Marionette.ItemView.extend({
    template: '#hrjob-tree-template'
  });
});
