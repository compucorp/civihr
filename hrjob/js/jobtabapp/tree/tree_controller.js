CRM.HRApp.module('JobTabApp.Tree', function(Tree, HRApp, Backbone, Marionette, $, _){
  Tree.Controller = {
    show: function(cid){
      var jobs = HRApp.request("hrjob:entities");
      var exampleView = new Tree.View({
        jobCollection: jobs
      });
      HRApp.treeRegion.show(exampleView);
    }
  }
});
