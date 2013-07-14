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
      'click .hrjob-role-toggle': 'toggleRole',
      'change .bindto-in': 'updateBinding'
    },
    modelEvents: {
      'change': 'onUpdateModel'
    },
    onRender: function() {
      this.onUpdateModel();
      this.$('.hrjob-role-toggle').addClass('closed');
      this.$('.toggle-role-form').hide();
    },
    updateBinding: function(event) {
      this.model.set($(event.target).attr('name'), $(event.target).val());
    },
    onUpdateModel: function() {
      var model = this.model;
      this.$('.bindto-out').each(function(){
        $(this).text(model.get($(this).attr('data-bindto')));
      });
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
