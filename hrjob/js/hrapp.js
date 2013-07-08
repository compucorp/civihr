CRM.HRApp = new Marionette.Application();

CRM.HRApp.addRegions({
  mainRegion: ".hrjob-main-region",
  treeRegion: ".hrjob-tree-region"
});

CRM.HRApp.navigate = function(route,  options){
  options || (options = {});
  Backbone.history.navigate(route, options);
};

CRM.HRApp.getCurrentRoute = function(){
  return Backbone.history.fragment
};

CRM.HRApp.on("initialize:after", function(){
  if(Backbone.history){
    Backbone.history.start();

    var exampleTreeView = new Backbone.Marionette.ItemView({
      template: '#hrjob-tree-template'
    });
    CRM.HRApp.treeRegion.show(exampleTreeView);

    if(this.getCurrentRoute() === ""){
      CRM.HRApp.trigger("intro:show", CRM.jobTabApp.contact_id);
    }
  }
});
