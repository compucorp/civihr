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
    }
  }
});
