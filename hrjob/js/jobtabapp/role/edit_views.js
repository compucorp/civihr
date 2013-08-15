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
      'click .hrjob-role-remove': 'toggleSoftDelete',
      'click .hrjob-role-restore': 'toggleSoftDelete',
      'click .hrjob-role-toggle': 'toggleRole'
    },
    modelEvents: {
      'softDelete': 'renderSoftDelete'
    },
    initialize: function() {
      CRM.HRApp.Common.mbind(this);
    },
    onRender: function() {
      this.$('.hrjob-role-toggle').addClass('closed');
      this.$('.toggle-role-form').hide();
      this.renderSoftDelete();

      var editView = new Role.EditView({
        model: this.model
      });
      this.toggledRegion.show(editView);
    },
    renderSoftDelete: function() {
      this.$el
        .toggleClass('deleted', this.model.isSoftDeleted())
        .toggleClass('undeleted', !this.model.isSoftDeleted());
    },
    toggleSoftDelete: function() {
      this.model.setSoftDeleted(!this.model.isSoftDeleted());
    },
    toggleRole: function() {
      var open = this.$('.hrjob-role-toggle').hasClass('closed');
      this.$('.hrjob-role-toggle').toggleClass('closed', !open);
      this.$('.hrjob-role-toggle').toggleClass('open', open);
      this.$('.toggle-role-form').toggle(open);
      if (open) {
        this.$('input,select,textarea').first().focus();
      }
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
    },
    onRender: function() {
      this.$('.crm-contact-selector').crmContactField();
    }
  });

  Role.TableView = Marionette.CompositeView.extend({
    itemView: Role.RowView,
    itemViewContainer: 'table.hrjob-role-table > tbody',
    template: '#hrjob-role-table-template',
    templateHelpers: function() {
      var isNew = this.collection.foldl(function(memo, model){
        return model.get('id') ? false : true;
      }, false);
      return {
        'isNew': isNew,
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJobRole
      };
    },
    events: {
      'click .hrjob-role-add': 'doAdd',
      'click .standard-save': 'doSave',
      'click .standard-reset': 'doReset'
    },
    initialize: function() {
      this.listenTo(HRApp, 'navigate:warnings', this.onNavigateWarnings);
    },
    onRender: function() {
      if (CRM.jobTabApp.isLogEnabled) {
        this.$('.hrjob-revision-link').crmRevisionLink({
	  reportId: CRM.jobTabApp.loggingReportId,
	  contactId: CRM.jobTabApp.contact_id,
	  tableName: this.$('.hrjob-revision-link').attr('data-table-name')
	});
      } else {
        this.$('.crm-revision-link').hide();
      }
    },
    appendHtml: function(collectionView, itemView, index) {
      collectionView.$('tr.hrjob-role-final').before(itemView.el);
    },
    doAdd: function(e) {
      e.stopPropagation();
      var model = new CRM.HRApp.Entities.HRJobRole(
        this.options.newModelDefaults || {}
      );
      this.collection.add(model);
      this.children.findByModel(model).toggleRole(); // open
      return false;
    },
    doSave: function() {
      var view = this;
      HRApp.trigger('ui:block', ts('Saving'));
      view.collection.save({
        success: function() {
          HRApp.trigger('ui:unblock');
          CRM.alert(ts('Saved'), null, 'success');
          view.triggerMethod('standard:save', view, view.model);
        },
        error: function() {
          HRApp.trigger('ui:unblock');
          // Note: CRM.Backbone.sync displays API errors with CRM.alert
        }
      });
    },
    doReset: function() {
      var view = this;
      HRApp.trigger('ui:block', ts('Loading'));
      this.collection.fetch({
        reset: true,
        success: function() {
          HRApp.trigger('ui:unblock');
          CRM.alert(ts('Reset'));
          view.triggerMethod('standard:reset', view, view.model);
        },
        error: function() {
          HRApp.trigger('ui:unblock');
          // Note: CRM.Backbone.sync displays API errors with CRM.alert
        }
      });
    },
    onNavigateWarnings: function(route, options) {
      // The "Role" table may include a mix of existing (modifiable) rows,
      // newly added rows, and deleted rows.
      var modified = this.collection.foldl(function(memo, model){
        return memo || model.isNew() || model.isModified() || model.isSoftDeleted();
      }, false);
      if (modified) {
        options.warnTitle = ts('Abandon Changes?');
        options.warnMessages.push(ts('There are unsaved changes! Are you sure you want to abandon the changes?'));
      }
    }
  });
});
