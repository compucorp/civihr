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
      'change:contract_file': 'renderContractFile'
    },
    initialize: function() {
      CRM.HRApp.Common.mbind(this);
    },
    onRender: function() {
      this.renderManagerContact();
      this.renderContractFile();
    },
    renderManagerContact: function() {
      this.$('a.hrjob-manager_contact').hrContactLink({
        cid: this.model.get('manager_contact_id')
      });
    },
    renderContractFile: function() {
      this.$('#contract_file').hrFileLink({
          id: this.model.get('id'),
          entityTable: "civicrm_hrjob_general"
      });
      this.$('.file-delete').remove();
    }
  });

});