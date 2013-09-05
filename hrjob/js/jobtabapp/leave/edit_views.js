// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRApp.module('JobTabApp.Leave', function(Leave, HRApp, Backbone, Marionette, $, _) {
  Leave.RowView = Marionette.ItemView.extend({
    tagName: 'tr',
    template: '#hrjob-leave-template',
    templateHelpers: function() {
      return {
        'cid': this.model.cid,
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJobLeave
      };
    },
    initialize: function() {
      CRM.HRApp.Common.mbind(this);
    },
    onBindingCreate: function(bindings) {
      // The field names in each <TR> must be distinct, so we append the cid.
      // However, ModelBinder doesn't know about the cid suffix, so we fix it.
      var suffix = '_' + this.model.cid;
      _.each(['leave_type', 'leave_amount'], function(field) {
        bindings[field] = bindings[field + suffix];
        delete bindings[field + suffix];
      });
    },
    onValidateRulesCreate: function(view, r) {
      var suffix = '_' + this.model.cid;
      r.rules['leave_amount' + suffix] = {
        required: true,
        number: true,
        range: [0, 365]
      };
    }
  });

  Leave.TableView = Marionette.CompositeView.extend({
    itemView: Leave.RowView,
    itemViewContainer: 'tbody',
    template: '#hrjob-leave-table-template',
    templateHelpers: function() {
      var isNew = this.collection.foldl(function(memo, model) {
        return memo && (model.get('id') ? false : true);
      }, true);
      return {
        'isNew': isNew,
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJobLeave
      };
    },
    initialize: function(models, options) {
      this.addMissingTypes();
      this.listenTo(HRApp, 'navigate:warnings', this.onNavigateWarnings);
    },
    events: {
      'click .standard-save': 'doSave',
      'click .standard-reset': 'doReset'
    },
    doSave: function() {
      var view = this;
      if (!this.$('form').valid()) {
        return false;
      }

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
      return false;
    },
    doReset: function() {
      var view = this;
      HRApp.trigger('ui:block', ts('Loading'));
      this.collection.fetch({
        reset: true,
        success: function() {
          HRApp.trigger('ui:unblock');
          view.addMissingTypes();
          view.render(); // CompositeView doesn't draw the new elements at the right position
          CRM.alert(ts('Reset'));
          view.triggerMethod('standard:reset', view, view.model);
        },
        error: function() {
          HRApp.trigger('ui:unblock');
          // Note: CRM.Backbone.sync displays API errors with CRM.alert
        }
      });
      return false;
    },
    addMissingTypes: function() {
      this.collection.addMissingTypes(
        _.keys(CRM.FieldOptions.HRJobLeave.leave_type),
        { job_id: this.collection.crmCriteria.job_id } // FIXME: tight coupling
      );
    },
    onNavigateWarnings: function(route, options) {
      // The "Leave" table shows a fixed number of rows -- in two general categories:
      // 1. Real, existing DB rows
      // 2. New, placeholder DB rows (which would become real if saved)
      // In both case, we only care if the row has been *modified*. Insertions/deletions
      // are a non-issue.
      var modified = this.collection.foldl(function(memo, model) {
        return memo || model.isModified();
      }, false);
      if (modified) {
        options.warnTitle = ts('Abandon Changes?');
        options.warnMessages.push(ts('There are unsaved changes! Are you sure you want to abandon the changes?'));
      }
    },
    onRender: function() {
      var rules = this.createValidationRules();
      this.$('form').validate(rules);
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
    /**
     *
     * @return {*} jQuery.validate rules
     */
    createValidationRules: function() {
      var rules = _.extend({}, CRM.validate.params);
      rules.rules || (rules.rules = {});
      this.triggerMethod("validateRules:create", this, rules);
      _.each(this.children.toArray(), function(child) {
        child.triggerMethod("validateRules:create", child, rules);
      });
      return rules;
    }
  });
});
