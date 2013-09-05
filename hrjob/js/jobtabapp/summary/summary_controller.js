// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRApp.module('JobTabApp.Summary', function(Summary, HRApp, Backbone, Marionette, $, _) {
  Summary.Controller = {
    showSummary: function(cid, jobId) {
      // Use CRM.api for now so that we can do chaining.
      // TODO: Backbone request batching
      HRApp.trigger('ui:block', ts('Loading'));
      CRM.api('HRJob', 'get', {
        id: jobId,
        'api.HRJobHealth.get': 1,
        'api.HRJobHour.get': 1,
        'api.HRJobLeave.get': 1,
        'api.HRJobPay.get': 1,
        'api.HRJobPension.get': 1,
        'api.HRJobRole.get': 1
      }, {
        success: function(result, ajax) {
          HRApp.trigger('ui:unblock');

          if (result.count != 1) {
            var treeView = new HRApp.Common.Views.Failed();
            HRApp.mainRegion.show(treeView);
            return false;
          }

          var models = Summary.Controller.parseChainedModels(jobId, result.values[result.id]);
          var mainView = new Summary.ShowView({
            model:  models.HRJob.first(),
            models: models
          });
          HRApp.mainRegion.show(mainView);
        },
        error: function(data) {
          // Override the default error handler so that we can unblock the UI
          HRApp.trigger('ui:unblock');

          // the default error handler would normally do this, but we've overriden
          // that, so we have to do it ourselves
          $().crmError(data.error_message, ts('Error'));
        }
      });
    },

    /**
     * Examine the response to "chained" API calls and parse each of
     * the returned entities.
     *
     * @param int jobId
     * @param {Object} attrs raw response from API
     * @return {Object} a set of Backbone Collections -- one Backbone Collection for each chained API call; keys are based on the entity-name
     */
    parseChainedModels: function(jobId, attrs) {
      var models = {};

      // Parse & remove any sub-entities from "attrs"
      _.each(['HRJobHealth', 'HRJobHour', 'HRJobLeave', 'HRJobPay', 'HRJobPension', 'HRJobRole'], function(entity) {
        var key = "api." + entity + ".get";
        var Collection = HRApp.Entities[entity + "Collection"];
        if (attrs[key]) {
          models[entity] = new Collection(attrs[key].values, {
            crmCriteria: {
              job_id: jobId
            }
          });
          delete attrs[key];
        } else {
          models[entity] = new Collection([], {
            crmCriteria: {
              job_id: jobId
            }
          });
        }
      });

      // Parse main entity
      models.HRJob = new HRApp.Entities.HRJobCollection([attrs], {
        crmCriteria: {
          id: jobId
        }
      });

      // Fin
      return models;
    }
  }
});
