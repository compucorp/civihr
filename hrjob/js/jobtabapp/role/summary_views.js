// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRApp.module('JobTabApp.Role', function(Role, HRApp, Backbone, Marionette, $, _) {

  Role.SummaryRowView = Marionette.Layout.extend({
    bindingAttribute: 'data-hrjobrole-row',
    tagName: 'tr',
    template: '#hrjob-role-summary-row-template',
    templateHelpers: function() {
      return {
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJobRole
      };
    },
    regions: {
      toggledRegion: '.toggle-role-form'
    },
    events: {
      'click .hrjob-role-toggle': 'toggleRole'
    },
    modelEvents: {
      'change:funder': 'renderFunder'
    },
    initialize: function() {
      CRM.HRApp.Common.mbind(this);
    },
    onRender: function() {
      this.$('.hrjob-role-toggle').addClass('closed');
      this.$('.toggle-role-form').hide();
      this.renderFunder();

      this.toggledRegion.show(new Role.SummaryView({
        model: this.model
      }));
    },
    toggleRole: function() {
      var open = this.$('.hrjob-role-toggle').hasClass('closed');
      this.$('.hrjob-role-toggle').toggleClass('open', open).toggleClass('closed', !open);
      this.$('.toggle-role-form').toggle(open);
    },
    renderFunder: function() {
      this.$('a.hrjob-funder').hrContactLink({
        cid: this.model.get('funder')
      });
    }
  });

  Role.SummaryView = Marionette.ItemView.extend({
    template: '#hrjob-role-summary-template',
    templateHelpers: function() {
      return {
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJobRole
      };
    },
    modelEvents: {
      'change:manager_contact_id': 'renderManagerContact',
      'change:funder': 'renderFunder'
    },
    initialize: function() {
      CRM.HRApp.Common.mbind(this);
    },
    onRender: function() {
      this.renderManagerContact();
      this.renderFunder();
    },
    renderManagerContact: function() {
      this.$('a.hrjob-manager_contact').hrContactLink({
        cid: this.model.get('manager_contact_id')
      });
    },
    renderFunder: function() {
      this.$('a.hrjob-funder').hrContactLink({
        cid: this.model.get('funder')
      });
    }
  });

  Role.SummaryTableView = Marionette.CompositeView.extend({
    itemView: Role.SummaryRowView,
    itemViewContainer: 'table.hrjob-role-table > tbody',
    template: '#hrjob-role-summary-table-template',
    templateHelpers: function() {
      return {
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJobRole
      };
    },
    collectionEvents: {
      'add': 'autoHide',
      'remove': 'autoHide',
      'reset': 'autoHide'
    },
    onRender: function() {
      this.autoHide();
    },
    autoHide: function() {
      if (this.collection.isEmpty()) {
        this.$el.hide();
      } else {
        this.$el.show();
      }
    }
  });
});