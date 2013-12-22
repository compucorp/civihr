// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRAbsenceApp.module('Tabs', function(Tabs, HRAbsenceApp, Backbone, Marionette, $, _) {
  Tabs.TabsView = Marionette.ItemView.extend({
    template: '#hrabsence-tabs-template',
    events: {
      "click .hrabsence-nav": function(e) {
        e.preventDefault();
        HRAbsenceApp.trigger($(e.currentTarget).attr('data-hrabsence-event'));
      }
    },
    initialize: function() {
      this.listenTo(HRAbsenceApp, 'navigate', this.render);
    },
    onRender: function() {
      //this.$('[data-highlight-on]').removeClass('highlight');
      this.$('[data-highlight-on="'+Backbone.history.fragment+'"]').addClass('highlight');
    }
  });
});
