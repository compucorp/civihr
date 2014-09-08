// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRApp.module('JobTabApp.Funding', function(Funding, HRApp, Backbone, Marionette, $, _) {
  Funding.Controller = {
    addFunding: function(cid, jobCollection) {
      var model = new HRApp.Entities.HRJob({
        contact_id: cid
      });
      var mainView = new Funding.EditView({
        model: model,
        collection: jobCollection
      });
      mainView.listenTo(mainView, "standard:save", function(view, model) {
        _.defer(function() {
          jobCollection.fetch(); // e.g. changes to model.is_primary can affect the entire collection
          CRM.HRApp.trigger("hrjob:funding:edit", model.get('contact_id'), model.get('id'));
        });
      });
      HRApp.mainRegion.show(mainView);
    },

    editFunding: function(cid, jobId, jobCollection) {
      HRApp.trigger('ui:block', ts('Loading'));
      var RoleCollection = new CRM.HRApp.Entities.HRJobRoleCollection([], {
        crmCriteria: {contact_id: cid, job_id: jobId},
      });

      var model = new HRApp.Entities.HRJob({id: jobId});
      model.fetch({
        success: function() {
          HRApp.trigger('ui:unblock');
          RoleCollection.fetch({
            success: function() {
              var mainView = new Funding.EditView({
                model: model,
                collection: jobCollection,
                roleCollection: RoleCollection
              });
              HRApp.mainRegion.show(mainView);
              mainView.listenTo(mainView, "standard:save", function(view, model) {
                jobCollection.fetch(); // e.g. changes to model.is_primary can affect the entire collection
              });
	    }
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
