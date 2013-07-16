CRM.HRApp.module('JobTabApp.Leave', function(Leave, HRApp, Backbone, Marionette, $, _){
  Leave.RowView = Marionette.ItemView.extend({
    tagName: 'tr',
    template: '#hrjob-leave-template',
    templateHelpers: function() {
      return {
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJobLeave
      };
    }
  });

  Leave.TableView = Marionette.CompositeView.extend({
    itemView: Leave.RowView,
    itemViewContainer: 'tbody',
    template: '#hrjob-leave-table-template',
    templateHelpers: function() {
      return {
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJobLeave
      };
    }
  });
});
