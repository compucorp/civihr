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

  Entities.HRJobCollection = Backbone.Collection.extend({
    model: Entities.HRJob
  });

  var initializeHRJobs = function(){
    var jobs = new Entities.HRJobCollection([
      { id: 41, position: 'Developer', title: 'Senior Associate for Extemperanous CSS', contract_type: 'Employee', level_type: 'Senior Staff', 'period_type': 'Permanent', 'period_start_date': '2010-01-02', 'period_end_date': '2012-03-04', 'manager_contact_id': 3 },
      { id: 42, position: 'Dancer', contract_type: 'Contractor', period_type: 'Temporary' },
      { id: 53, position: 'Dentist', contract_type: 'Volunteer' }
    ]);
    return jobs;
  };
  var jobs = initializeHRJobs();

  var API = {
    getHRJobEntity: function(jobId) {
      var entities = API.getHRJobEntities();
      return entities.get(jobId);
    },
    getHRJobEntities: function(){
      return jobs;
      /*
      var defer = $.Deferred();
      setTimeout(function(){
        var data = initializeHRJobs();
        defer.resolve(data);
      }, 500);
      var promise = defer.promise();
      return promise;
      */
    }
  };

  HRApp.reqres.setHandler("hrjob:entities", function(){
    return API.getHRJobEntities();
  });
  HRApp.reqres.setHandler("hrjob:entity", function(jobId){
    return API.getHRJobEntity(jobId);
  });

  // FIXME real models
  _.each(['HRJobHealth', 'HRJobHour', 'HRJobLeave', 'HRJobPay', 'HRJobPension', 'HRJobRole'], function(entityName){
    Entities[entityName] = Backbone.Model.extend({
    });
    Entities[entityName + "Collection"] = Backbone.Collection.extend({
      model: Entities[entityName]
    });
  });
});
