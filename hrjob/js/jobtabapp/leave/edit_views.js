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
      _.each(['leave_type', 'leave_amount'], function(field){
        bindings[field] = bindings[field + suffix];
        delete bindings[field + suffix];
      });
    }
  });

  Leave.TableView = Marionette.CompositeView.extend({
    itemView: Leave.RowView,
    itemViewContainer: 'tbody',
    template: '#hrjob-leave-table-template',
    templateHelpers: function() {
      return {
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
      var modified = this.collection.foldl(function(memo, model){
        return memo || model.isModified();
      }, false);
      if (modified) {
        options.warnTitle = ts('Abandon Changes?');
        options.warnMessages.push(ts('There are unsaved changes! Are you sure you want to abandon the changes?'));
      }
    }
  });
});
