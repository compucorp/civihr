// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRApp.module('JobTabApp.Tree', function(Tree, HRApp, Backbone, Marionette, $, _) {
  Tree.ItemView = Marionette.ItemView.extend({
    template: '#hrjob-tree-item-template',
    templateHelpers: function() {
      return {
        is_active: this.model.isActive(),
        cid: CRM.jobTabApp.contact_id // FIXME
      }
    },
    modelEvents: {
      'change:is_primary': 'render'
    },
    events: {
      'click .hrjob-nav': 'doTriggerEvent'
    },
    initialize: function() {
      CRM.HRApp.Common.mbind(this);
    },
    doTriggerEvent: function(e) {
      e.preventDefault();
      /*
      console.log("goto",
        $(e.currentTarget).attr('data-hrjob-event'),
        CRM.jobTabApp.contact_id, // FIXME
        this.model.get('id')
      );
      */
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
    initialize: function() {
      this.listenTo(this, 'render', this.showHide);
      this.listenTo(this, 'after:item:added', this.showHide);
      this.listenTo(this.collection, 'sync', this.render);
    },
    doAddJob: function(e) {
      e.preventDefault();
      CRM.HRApp.trigger(
        'hrjob:add',
        CRM.jobTabApp.contact_id // FIXME
      );
    },
    showHide: function() {
      if (this.collection.isEmpty()) {
        this.$el.hide();
      } else {
        this.$el.show();
      }
    },
    onRender: function() {
      this.selectRoute(CRM.HRApp.Common.Navigation.getCurrentRoute());
      CRM.tabHeader.updateCount('#tab_hrjob', this.collection.length);
    },
    /**
     * Designate a particular path (eg "#9/hrjob/10/pay")
     * as active
     *
     * @param path
     */
    selectRoute: function(route) {
      this.$('.selected').removeClass('selected');
      this.$('a').each(function() {
        var $this = $(this);
        if ($this.attr('href') == ('#' + route)) {
          $this.addClass('selected');
        }
      });
    }
  });
});
