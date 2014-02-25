// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRApp.module('JobTabApp.Health', function(Health, HRApp, Backbone, Marionette, $, _) {
  Health.SummaryView = Marionette.ItemView.extend({
    templateHelpers: function() {
      return {
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions[this.options.crmEntityName]
      };
    },
    modelEvents: {
      'change:provider': 'renderProvider',
      'change:life_insurance_provider': 'renderProvider'
    },
    initialize: function() {
      CRM.HRApp.Common.mbind(this);
    },
    onRender: function() {
      this.renderProvider();
    },
    renderProvider: function() {
      this.$('a.hrjob-provider').hrContactLink({
        cid: this.model.get('provider')
      });
      this.$('a.hrjob-provider_life_insurance').hrContactLink({
        cid: this.model.get('provider_life_insurance')
      });
    }
  });

});