// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRApp.module('JobTabApp.General', function(General, HRApp, Backbone, Marionette, $, _) {
  General.SummaryView = Marionette.ItemView.extend({
    template: '#hrjob-general-summary-template',
    templateHelpers: function() {
      return {
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJob
      };
    },
    modelEvents: {
      'change:manager_contact_id': 'renderManagerContact',
      'change:funding_org_id': 'renderFundingOrg'
    },
    initialize: function() {
      CRM.HRApp.Common.mbind(this);
    },
    onRender: function() {
      this.renderManagerContact();
      this.renderFundingOrg();
    },
    renderManagerContact: function() {
      this.$('a.hrjob-manager_contact').hrContactLink({
        cid: this.model.get('manager_contact_id')
      });
    },
    renderFundingOrg: function() { 
      this.$('a.hrjob-funding_org_id').hrContactLink({	  
        cid: this.model.get('funding_org_id')
      });
    }
  });

});