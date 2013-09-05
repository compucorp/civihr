// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRApp.module('JobTabApp.General', function(General, HRApp, Backbone, Marionette, $, _) {
  General.Controller = {
    addGeneral: function(cid, jobCollection) {
      var model = new HRApp.Entities.HRJob({
        contact_id: cid,
        is_primary: jobCollection.isEmpty()
      });
      var mainView = new General.EditView({
        model: model,
        collection: jobCollection
      });
      mainView.listenTo(mainView, "standard:save", function(view, model) {
        _.defer(function() {
          jobCollection.fetch(); // e.g. changes to model.is_primary can affect the entire collection
          CRM.HRApp.trigger("hrjob:general:edit", model.get('contact_id'), model.get('id'));
        });
      });
      HRApp.mainRegion.show(mainView);
    },

    editGeneral: function(cid, jobId, jobCollection) {
      HRApp.trigger('ui:block', ts('Loading'));
      var model = new HRApp.Entities.HRJob({id: jobId});
      model.fetch({
        success: function() {
          HRApp.trigger('ui:unblock');
          var mainView = new General.EditView({
            model: model,
            collection: jobCollection
          });
          HRApp.mainRegion.show(mainView);
          mainView.listenTo(mainView, "standard:save", function(view, model) {
            jobCollection.fetch(); // e.g. changes to model.is_primary can affect the entire collection
          });
        },
        error: function() {
          HRApp.trigger('ui:unblock');
          var treeView = new HRApp.Common.Views.Failed();
          HRApp.mainRegion.show(treeView);
        }
      });
    },

    /**
     * Display a form pre-populated with details of an existing
     * job; allow user to edit/tweak and save as a new copy.
     *
     * @param HRJobModel job
     * @param HRJobCollection jobCollection
     */
    copyGeneral: function(cid, jobId, jobCollection) {
      HRApp.trigger('ui:block', ts('Loading'));
      var origModel = new HRApp.Entities.HRJob({id: jobId});
      origModel.fetch({
        success: function() {
          HRApp.trigger('ui:unblock');
          var model = origModel.duplicate();
          model.set('is_primary', '0');
          var mainView = new General.EditView({
            model: model,
            collection: jobCollection
          });
          HRApp.mainRegion.show(mainView);
          mainView.listenTo(mainView, "standard:save", function(view, model) {
            _.defer(function() {
              jobCollection.fetch(); // e.g. changes to model.is_primary can affect the entire collection
              CRM.HRApp.trigger("hrjob:general:edit", model.get('contact_id'), model.get('id'));
            });
          });
        },
        error: function() {
          HRApp.trigger('ui:unblock');
          var view = new HRApp.Common.Views.Failed();
          HRApp.mainRegion.show(view);
        }
      });
    }
  }
});
