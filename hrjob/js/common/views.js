// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRApp.module('Common.Views', function(Views, HRApp, Backbone, Marionette, $, _) {
  Views.Failed = Marionette.ItemView.extend({
    template: "#common-failed-template"
  });

  /**
   * An standard form is a basic CRUD form in which the fields are bound
   * to a single model -- and for which save/reset operations can be invoked
   * through a button.
   *
   * Events:
   *  - standard:save: function(view,model)
   *  - standard:reset: function(view,model)
   *
   * @type {*}
   */
  Views.StandardForm = Marionette.ItemView.extend({
    initialize: function() {
      this.modelBackup = this.model.toJSON();
      CRM.HRApp.Common.mbind(this);
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
      var rules = this.createValidationRules();
      this.$('form').validate(rules);
      if (rules.rules) {
        var view = this;
        _.each(rules.rules, function(rule, field) {
          var $label = view.$('[name=' + field + ']').parents('.crm-summary-row').find('.crm-label');
          if (rule.required && !$label.data('has-required')) {
            $label.data('has-required', true);
            $label.append(HRApp.RenderUtil.required());
            $label.siblings('.crm-content').find('input, select').addClass('required');
          }
        });
      }
      // Attach data needed for optionList editing
      this.$('a.crm-option-edit-link').each(function() {
        $(this).siblings('select').attr('data-option-edit-path', $(this).data('option-edit-path'));
      });
      // Needed for re-rendering
      $(this.$el).trigger('crmLoad');
    },
    // Needed for initial render
    onShow: function() {
      $(this.$el).trigger('crmLoad');
    },
    /**
     *
     * @return {*} jQuery.validate rules
     */
    createValidationRules: function() {
      var rules = _.extend({}, CRM.validate.params);
      rules.rules || (rules.rules = {});
      this.triggerMethod("validateRules:create", this, rules);
      return rules;
    },
    modelEvents: {
      invalid: function(model, errors) {
        var view = this;
        _.each(errors, function(message, field) {
          view.$('[name=' + field + ']').crmError(message);
        });
      }
    },
    events: {
      'click .standard-save': 'doSave',
      'click .standard-reset': 'doReset',
      'crmOptionsUpdated select': 'updateOptions'
    },
    doSave: function() {
      var view = this;
      if (!this.$('form').valid() || !view.model.isValid()) {
        return false;
      }

      HRApp.trigger('ui:block', ts('Saving'));
      this.model.save({}, {
        success: function() {
          HRApp.trigger('ui:unblock');
          CRM.alert(ts('Saved'), null, 'success');
          view.modelBackup = view.model.toJSON();
          view.render();
          view.triggerMethod('standard:save', view, view.model);
        },
        error: function() {
          HRApp.trigger('ui:block', ts('Error while saving. Please reload and retry.'));
        }
      });
      return false;
    },
    doReset: function() {
      CRM.alert(ts('Reset'));
      var view = this;
      this.model.clear();
      this.model.set(this.modelBackup);
      view.render();
      view.triggerMethod('standard:reset', view, view.model);
      return false;
    },
    onNavigateWarnings: function(route, options) {
      if (this.model.isModified()) {
        options.warnTitle = ts('Abandon Changes?');
        options.warnMessages.push(ts('There are unsaved changes! Are you sure you want to abandon the changes?'));
      }
    },
    // This is triggered after an option list is edited
    updateOptions: function(e, options) {
      var
        $el = $(e.target),
        entity = $el.data('api-entity'),
        field = $el.data('api-field'),
      // FIXME: really we should always be working with option lists as arrays
      // Since we're not doing that, here's a hack to convert the array to an object
        opts = _.mapValues(_.indexBy(options, 'key'), 'value');
      CRM.FieldOptions[entity][field] = opts;
    }
  });

  /**
   * An auto-save form is a basic CRUD form in which the fields are bound
   * to a model -- and in which changes are saved within a few seconds.
   *
   * @type {*}
   *
   Views.AutoSaveForm = Marionette.ItemView.extend({
    saveState: 'saved', // saved, unsaved, active, error
    pendingAlert: null,
    initialize: function() {
      CRM.HRApp.Common.mbind(this);
      var view = this;
      var model = this.model;
      this.throttledSave = _.throttle(function(){
        this.setSaveSate('active');
        model.save({}, {
          success: function() {
            view.setSaveSate('saved');
          },
          error: function() {
            view.setSaveSate('error');
          }
        });
      }, 3000, {
        leading: false
      });
    },
    onRender: function() {
      this.setSaveSate(this.saveState);
    },
    modelEvents: {
      'change': function() {
        this.setSaveSate('unsaved');
        this.throttledSave();
      }
    },
    setSaveSate: function(state) {
      // this.trigger('change:saveState', this.saveState, state);
      this.saveState = state;
      $(this.el)
        .removeClass('autosave-saved autosave-unsaved autosave-active autosave-error')
        .addClass('autosave-' + state);

      // FIXME this looks a bit weird; make something nicer
      this.pendingAlert && this.pendingAlert.close();
      this.pendingAlert = null;
      switch (state) {
        case 'saved':
          break;
        case 'unsaved':
          this.pendingAlert = CRM.alert('Unsaved');
          break;
        case 'active':
          this.pendingAlert = CRM.alert('Saving');
          break;
        case 'error':
          this.pendingAlert = CRM.alert('Error');
          break;
        default:
      }
    }
  }); */
});
