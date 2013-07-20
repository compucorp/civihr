CRM.HRApp.module('Common.Views', function(Views, HRApp, Backbone, Marionette, $, _){
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
      CRM.HRApp.Common.mbind(this);
    },
    onRender: function() {
    },
    events: {
      'click .standard-save': function() {
        var view = this;
        HRApp.trigger('ui:block', ts('Saving'));
        this.model.save({}, {
          success: function() {
            HRApp.trigger('ui:unblock');
            view.render();
            view.trigger('standard:save', view, view.model);
          },
          error: function() {
            HRApp.trigger('ui:block', ts('Error while saving. Please reload and retry.'));
          }
        });
      },
      'click .standard-reset': function() {
        var view = this;
        HRApp.trigger('ui:block', ts('Reloading'));
        this.model.fetch({
          success: function() {
            HRApp.trigger('ui:unblock');
            view.render();
            view.trigger('standard:reset', view, view.model);
          },
          error: function() {
            HRApp.trigger('ui:block', ts('Error while saving. Please reload and retry.'));
          }
        });
      }
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