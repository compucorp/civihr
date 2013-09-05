// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRApp.module('JobTabApp.Intro', function(Intro, HRApp, Backbone, Marionette, $, _){
  Intro.ShowView = Marionette.ItemView.extend({
    template: '#hrjob-intro-template',
    templateHelpers: function() {
      return {
        'RenderUtil': CRM.HRApp.RenderUtil,
        hrjob_add_url: '#' + this.options.contact_id + '/hrjob/add'
      };
    },
    events: {
      "click .hrjob-add": function(e) {
        e.preventDefault();
        CRM.HRApp.trigger(
          'hrjob:add',
          this.options.contact_id
        );
      }
    }
  });
});
