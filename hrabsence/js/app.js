// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRAbsenceApp = new Marionette.Application();

CRM.HRAbsenceApp.addRegions({
  newRegion: ".hrabsence-new-region",
  filterRegion: ".hrabsence-filter-region",
  mainRegion: ".hrabsence-main-region"
});

CRM.HRAbsenceApp.on("initialize:after", function() {
  if (Backbone.history) {
    Backbone.history.start();
  }
});

CRM.HRAbsenceApp.on("navigate", function(route, options) {
  CRM.HRApp.messageRegion.close();
});

CRM.HRAbsenceApp.on("ui:block", function(message) {
  // cj('.hrjob-container').block({
  //   message: message
  // });
  cj.blockUI({
    css: { top: '50px', left: '', right: '50px' },
    message: null // disregard: message
  });
});

CRM.HRAbsenceApp.on("ui:unblock", function() {
  // cj('.hrjob-container').unblock();
  cj.unblockUI();
});
