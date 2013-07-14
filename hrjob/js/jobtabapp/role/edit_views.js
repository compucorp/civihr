CRM.HRApp.module('JobTabApp.Role', function(Role, HRApp, Backbone, Marionette, $, _){
  Role.RowView = Marionette.ItemView.extend({
    tagName: 'tr',
    template: '#hrjob-role-template',
    templateHelpers: function() {
      return {
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJobRole
      };
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
      var bindings = Backbone.ModelBinder.createDefaultBindings(this.el, 'data-hrjobrole');
      this.modelBinder.bind(this.model, this.el, bindings);
    },
    toggleRole: function() {
      this.$('.hrjob-role-toggle').toggleClass('closed');
      this.$('.hrjob-role-toggle').toggleClass('open');
      this.$('.toggle-role-form').toggle();
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
