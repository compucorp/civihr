CRM.HRApp.module('Entities', function(Entities, HRApp, Backbone, Marionette, $, _){
  Entities.HRJob = Backbone.Model.extend({
    defaults: {
      contact_id: null,
      position: '',
      title: '',
      is_tied_to_funding: 0,
      contract_type: null,
      seniority: null,
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
      { id: 1, position: 'Developer', contract_type: 'Employee' },
      { id: 2, position: 'Dancer', contract_type: 'Contractor' },
      { id: 3, position: 'Dentist', contract_type: 'Volunteer' }
    ]);
    return jobs.models;
  };

  var API = {
    getHRJobEntities: function(){
      return initializeHRJobs();
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
});
