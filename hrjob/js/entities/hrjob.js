CRM.HRApp.module('Entities', function(Entities, HRApp, Backbone, Marionette, $, _){
  Entities.HRJob = Backbone.Model.extend({
    defaults: {
      contact_id: null,
      position: '',
      title: '',
      is_tied_to_funding: 0,
      contract_type: null,
      level_type: null,
      period_type: null,
      period_start_date: '',
      period_end_date: '',
      manager_contact_id: null,
      is_primary: 0
    },

    validate: function(attrs, options) {
      var errors = {}
      if (! attrs.position) {
        errors.position = "can't be blank";
      }
      if( ! _.isEmpty(errors)){
        return errors;
      }
    }
  });
  CRM.Backbone.extendModel(Entities.HRJob, 'HRJob');

  Entities.HRJobCollection = Backbone.Collection.extend({
    sync: CRM.Backbone.sync,
    model: Entities.HRJob
  });
  CRM.Backbone.extendCollection(Entities.HRJobCollection);

  // FIXME real models
  _.each(['HRJobHealth', 'HRJobHour', 'HRJobLeave', 'HRJobPay', 'HRJobPension', 'HRJobRole'], function(entityName){
    Entities[entityName] = Backbone.Model.extend({
    });
    CRM.Backbone.extendModel(Entities[entityName], entityName);

    Entities[entityName + "Collection"] = Backbone.Collection.extend({
      model: Entities[entityName]
    });
    CRM.Backbone.extendCollection(Entities[entityName + "Collection"]);
  });
});
