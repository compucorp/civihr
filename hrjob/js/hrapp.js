CRM.HRApp = new Marionette.Application();

CRM.HRApp.addRegions({
  messageRegion: ".hrjob-message-region",
  mainRegion: ".hrjob-main-region",
  treeRegion: ".hrjob-tree-region"
});

CRM.HRApp.on("initialize:after", function() {
  if (Backbone.history) {
    Backbone.history.start();
  }
});

CRM.HRApp.on("navigate", function(route, options) {
  CRM.HRApp.messageRegion.close();
});

CRM.HRApp.on("ui:block", function(message) {
  // cj('.hrjob-container').block({
  //   message: message
  // });
  cj.blockUI({
    css: { top: '50px', left: '', right: '50px' },
    message: null // disregard: message
  });
});
CRM.HRApp.on("ui:unblock", function() {
  // cj('.hrjob-container').unblock();
  cj.unblockUI();
});
