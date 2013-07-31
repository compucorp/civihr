CRM.HRApp.module('JobTabApp.General', function(General, HRApp, Backbone, Marionette, $, _) {
  General.EditView = HRApp.Common.Views.StandardForm.extend({
    template: '#hrjob-general-template',
    templateHelpers: function() {
      return {
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJob
      };
    },
    initialize: function() {
      HRApp.Common.Views.StandardForm.prototype.initialize.apply(this, arguments);
      this.listenTo(this.options.collection, 'sync', this.toggleIsPrimary);
    },
    onRender: function() {
      this.toggleIsPrimary();
    },
    /**
     * Activate or de-activate is_primary based on collectino size
     */
    toggleIsPrimary: function() {
      var jobCount = this.options.collection.length;
      if (!this.options.collection.get(this.model)) {
        jobCount++;
      }
      if (jobCount <= 1) {
        this.$('[name=is_primary]').attr('disabled', true);
      } else {
        this.$('[name=is_primary]').attr('disabled', false);
      }
    }
  });
});
