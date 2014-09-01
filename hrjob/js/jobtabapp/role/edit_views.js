// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRApp.module('JobTabApp.Role', function(Role, HRApp, Backbone, Marionette, $, _) {
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
      if (open) {
        this.render();
      }
      this.$('.hrjob-role-toggle').toggleClass('open', open).toggleClass('closed', !open);
      this.$('.toggle-role-form').toggle(open);
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
      $(this.$el).trigger('crmLoad');
      var payCollection = new CRM.HRApp.Entities.HRJobPayCollection([], {
        crmCriteria: {contact_id: CRM.jobTabApp.contact_id, job_id: this.model.get('job_id')},
      });
      payCollection.fetch({
        success: function(e) {
          var pay = payCollection.first(),
	    totalPay = 0;
          if (pay && pay.get("pay_grade") == "paid" ) {
	    totalPay = pay.get("pay_currency")+' '+pay.get("pay_amount")+' per '+pay.get("pay_unit");
	  $('input[name="total_pay_currency"]').val(pay.get("pay_currency"));
	  $('input[name="total_pay_amount"]').val(pay.get("pay_amount"));
	  $('input[name="total_pay_unit"]').val(pay.get("pay_unit"));
	  }
	  $('input[name="total_pay"]').val(totalPay);
        },
      });
    },
    onShow: function() {
      $(this.$el).trigger('crmLoad');
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
        this.$('.hrjob-revision-link').hide();
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
          view.render();
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
          view.render();
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
      var modified = this.collection.foldl(function(memo, model) {
        return memo || model.isNew() || model.isModified() || model.isSoftDeleted();
      }, false);
      if (modified) {
        options.warnTitle = ts('Abandon Changes?');
        options.warnMessages.push(ts('There are unsaved changes! Are you sure you want to abandon the changes?'));
      }
    }
  });
});
