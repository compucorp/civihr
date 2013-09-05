// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRApp.module('JobTabApp.Leave', function(Leave, HRApp, Backbone, Marionette, $, _) {
  Leave.SummaryItemView = Marionette.ItemView.extend({
    template: '#hrjob-leave-summary-item-template',
    templateHelpers: function() {
      return {
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJobLeave
      };
    },
    modelEvents: {
      'change': 'render'
    },
    initialize: function() {
      CRM.HRApp.Common.mbind(this);
    },
    onRender: function() {
      if (this.model.get('leave_amount') && this.model.get('leave_amount') > 0) {
        this.$el.show();
      } else {
        this.$el.hide();
      }
    }
  });

  Leave.SummaryView = Marionette.CompositeView.extend({
    itemView: Leave.SummaryItemView,
    itemViewContainer: '.leave-items',
    template: '#hrjob-leave-summary-template',
    templateHelpers: function() {
      return {
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJobLeave
      };
    }
  });
});