CRM.HRApp.module('JobTabApp.Role', function(Role, HRApp, Backbone, Marionette, $, _){
  Role.RowView = Marionette.Layout.extend({
    bindingAttribute: 'data-hrjobrole-row',
    tagName: 'tr',
    template: '#hrjob-role-row-template',
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
      'click .hrjob-role-remove': 'removeRole',
      'click .hrjob-role-toggle': 'toggleRole'
    },
    initialize: function() {
      CRM.HRApp.Common.mbind(this);
    },
    onRender: function() {
      this.$('.hrjob-role-toggle').addClass('closed');
      this.$('.toggle-role-form').hide();

      var editView = new Role.EditView({
        model: this.model
      });
      this.toggledRegion.show(editView);
    },
    removeRole: function() {
      this.model.destroy();
    },
    toggleRole: function() {
      this.$('.hrjob-role-toggle').toggleClass('closed');
      this.$('.hrjob-role-toggle').toggleClass('open');
      this.$('.toggle-role-form').toggle();
    }
  });

  Role.EditView = Marionette.ItemView.extend({
    template: '#hrjob-role-template',
    templateHelpers: function() {
      return {
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJobRole
      };
    },
    initialize: function() {
      CRM.HRApp.Common.mbind(this);
    }
  });

  Role.TableView = Marionette.CompositeView.extend({
    itemView: Role.RowView,
    itemViewContainer: 'table.hrjob-role-table > tbody',
    template: '#hrjob-role-table-template',
    templateHelpers: function() {
      return {
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJobRole
      };
    }
  });
});
