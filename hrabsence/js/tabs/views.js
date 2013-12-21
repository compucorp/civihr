// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRAbsenceApp.module('Tabs', function(Tabs, HRAbsenceApp, Backbone, Marionette, $, _) {
  Tabs.TabsView = Marionette.ItemView.extend({
    template: '#hrabsence-tabs-template'
    // FIXME: implement tabs and subviews
  });
});