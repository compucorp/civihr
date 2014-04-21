// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
(function ($, _) {
  CRM.HRApp = new Marionette.Application();

  CRM.HRApp.addRegions({
    messageRegion: ".hrjob-message-region",
    mainRegion: ".hrjob-main-region",
    treeRegion: ".hrjob-tree-region",
    dialogRegion: ".hrjob-dialog-region"
  });

  CRM.HRApp.on("initialize:after", function() {
    if (Backbone.history) {
      if(Backbone.History.started) {
        Backbone.history.stop();
      }
      Backbone.history.start();
    }
  });

  CRM.HRApp.on("navigate", function(route, options) {
    CRM.HRApp.messageRegion.close();
  });

  CRM.HRApp.on("ui:block", function(message) {
    // $('.hrjob-container').block({
    //   message: message
    // });
    CRM.$.blockUI({
      css: { top: '50px', left: '', right: '50px' },
      message: null // disregard: message
    });
  });
  CRM.HRApp.on("ui:unblock", function() {
    // $('.hrjob-container').unblock();
    CRM.$.unblockUI();
  });
}(CRM.$, CRM._));
