CRM.HRApp.module('JobTabApp.General', function(General, HRApp, Backbone, Marionette, $, _) {
  General.Controller = {
    addGeneral: function(cid, jobCollection) {
      var model = new HRApp.Entities.HRJob({
        contact_id: cid
      });
      var mainView = new General.EditView({
        model: model
      });
      mainView.listenTo(mainView, "standard:save", function(view, model) {
        _.defer(function() {
          if (!jobCollection.get(model)) {
            jobCollection.add(model);
          }
          CRM.HRApp.trigger("hrjob:general:edit", model.get('contact_id'), model.get('id'));
        });
      });
      HRApp.mainRegion.show(mainView);
    },

    editGeneral: function(cid, jobId) {
      HRApp.trigger('ui:block', ts('Loading'));
      var model = new HRApp.Entities.HRJob({id: jobId});
      model.fetch({
        success: function() {
          HRApp.trigger('ui:unblock');
          var mainView = new General.EditView({
            model: model
          });
          HRApp.mainRegion.show(mainView);
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
