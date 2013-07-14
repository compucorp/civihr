CRM.HRApp.module('JobTabApp.Role', function(Role, HRApp, Backbone, Marionette, $, _){
  Role.RowView = Marionette.Layout.extend({
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
      'click .hrjob-role-toggle': 'toggleRole'
    },
    initialize: function() {
      this.modelBinder = new Backbone.ModelBinder();
    },
    onRender: function() {
      this.$('.hrjob-role-toggle').addClass('closed');
      this.$('.toggle-role-form').hide();

      var bindings = Backbone.ModelBinder.createDefaultBindings(this.el, 'data-hrjobrole-row');
      this.modelBinder.bind(this.model, this.el, bindings);

      var editView = new Role.EditView({
        model: this.model
      });
      this.toggledRegion.show(editView);
    },
    onClose: function() {
      this.modelBinder.unbind();
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
      this.modelBinder = new Backbone.ModelBinder();
    },
    onRender: function() {
      var bindings = Backbone.ModelBinder.createDefaultBindings(this.el, 'data-hrjobrole');
      this.modelBinder.bind(this.model, this.el, bindings);
    },
    onClose: function() {
      this.modelBinder.unbind();
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
