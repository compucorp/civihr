CRM.HRApp.module('JobTabApp.Tree', function(Tree, HRApp, Backbone, Marionette, $, _){
  Tree.ItemView = Marionette.ItemView.extend({
    template: '#hrjob-tree-item-template',
    templateHelpers: function() {
      return {
        cid: CRM.jobTabApp.contact_id // FIXME
      }
    },
    events: {
      'click .hrjob-nav': 'doTriggerEvent'
    },
    initialize: function() {
      CRM.HRApp.Common.mbind(this);
    },
    doTriggerEvent: function(e) {
      e.preventDefault();
      console.log("goto",
        $(e.currentTarget).attr('data-hrjob-event'),
        CRM.jobTabApp.contact_id, // FIXME
        this.model.get('id')
      );
      CRM.HRApp.trigger(
        $(e.currentTarget).attr('data-hrjob-event'),
        CRM.jobTabApp.contact_id, // FIXME
        this.model.get('id')
      );
    }
  });

  Tree.View = Marionette.CompositeView.extend({
    template: '#hrjob-tree-template',
    itemView: Tree.ItemView,
    itemViewContainer: '.hrjob-tree-items',
    events: {
      'click .hrjob-tree-add': 'doAddJob'
    },
    doAddJob: function(e) {
      e.preventDefault();
      CRM.HRApp.trigger(
        'hrjob:add',
        CRM.jobTabApp.contact_id // FIXME
      );
    }
  });
});
