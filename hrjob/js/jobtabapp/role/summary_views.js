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
    initialize: function() {
      CRM.HRApp.Common.mbind(this);
    },
    onRender: function() {
      this.$('.hrjob-role-toggle').addClass('closed');
      this.$('.toggle-role-form').hide();

      this.toggledRegion.show(new Role.SummaryView({
        model: this.model
      }));
    },
    toggleRole: function() {
      var open = this.$('.hrjob-role-toggle').hasClass('closed');
      this.$('.hrjob-role-toggle').toggleClass('closed', !open);
      this.$('.hrjob-role-toggle').toggleClass('open', open);
      this.$('.toggle-role-form').toggle(open);
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
    initialize: function() {
      CRM.HRApp.Common.mbind(this);
    },
    onRender: function() {
      this.$('.crm-contact-selector').crmContactField();
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
    }
  });
});