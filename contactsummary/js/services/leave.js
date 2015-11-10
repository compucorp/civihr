define(['services/services', 'lodash'], function (services, _) {
  'use strict';

  /**
   * @param {ApiService} Api
   * @param {ModelService} Model
   * @param {ContactDetailsService} ContactDetails
   * @param $q
   * @param $log
   * @returns {ModelService|Object|*}
   * @constructor
   */
  function LeaveService(Api, Model, ContactDetails, $q, $log, $filter) {
    $log.debug('Service: LeaveService');

    ////////////////////
    // Public Members //
    ////////////////////

    /**
     * @ngdoc service
     * @name LeaveService
     */
    //var factory = Model.createInstance();
    var factory = {};

    factory.collection = {
      items: {},
      insertItem: function (key, item) {
        this.items[key] = item;
      },
      getItem: function (key) {
        return this.items[key];
      },
      set: function (collection) {
        this.items = collection;
      },
      get: function () {
        return this.items;
      }
    };

    factory.getCollection = function () {
      return this.collection.get();
    };


    /**
     * @ngdoc method
     * @name get
     * @methodOf LeaveService
     * @returns {*}
     */
    factory.get = function () {
      /** @type {(LeaveService|ModelService)} */
      var self = this;

      return init(periodId).then(function () {
        return self.getData();
      });
    };

    factory.getCurrent = function () {
      /** @type {(LeaveService|ModelService)} */
      var self = this;
      var deferred = $q.defer(), periodId;

      getCurrentPeriod()
        .then(function (response) {
          if (response.hasOwnProperty('id')) {
            periodId = response.id;

            init(periodId).then(function () {
              deferred.resolve(self.collection.getItem(periodId));
            })
          } else {
            deferred.resolve({});
          }
        });

      return deferred.promise;
    };

    factory.getPrevious = function () {
      /** @type {(LeaveService|ModelService)} */
      var self = this;
      var deferred = $q.defer(), periodId;

      getPreviousPeriod()
        .then(function (response) {
          if (response.hasOwnProperty('id')) {
            periodId = response.id;

            init(periodId).then(function () {
              deferred.resolve(self.collection.getItem(periodId));
            })
          } else {
            deferred.resolve({});
          }
        });

      return deferred.promise;
    };

    /**
     * @ngdoc method
     * @name getEntitlement
     * @methodOf LeaveService
     */
    factory.getEntitlement = function (periodId) {
      var deferred = $q.defer();

      ContactDetails.get()
        .then(function (response) {
          var data = {contact_id: response.id, period_id: periodId, options: {'absence-range': 1}};

          return Api.get('HRAbsenceEntitlement', data);
        })
        .then(function (response) {
          if (response.values.length === 0) return {};

          entitlements = response.values;

          deferred.resolve(entitlements);
        });

      return deferred.promise;
    };

    /**
     * @ngdoc method
     * @name getAbsences
     * @methodOf LeaveService
     * @returns {*}
     */
    factory.getAbsences = function (periodId) {
      var deferred = $q.defer();

      var contactId;

      ContactDetails.get()
        .then(function (response) {
          contactId = response.id;

          return getPeriods();
        })
        .then(function (response) {
          var data = {
            target_contact_id: contactId,
            period_id: [periodId],
            options: {'absence-range': 1},
            sequential: 0 // this is *important* in order to get absences in correct format!
          };

          return Api.post('Activity', data, 'getabsences');
        })
        .then(function (response) {
          if (response.values.length === 0) return deferred.reject('No absences found');

          absences = response.values;

          deferred.resolve(absences);
        });

      return deferred.promise;
    };

    //var deferreds = {};

    /**
     * @ngdoc method
     * @name getAbsenceTypes
     * @methodOf LeaveService
     */
    factory.getAbsenceTypes = function () {
      // todo
      //if (!deferreds.hasOwnProperty('absenceTypes')) {
      //  deferreds.absenceTypes = $q.defer();
      //}

      var deferred = $q.defer();

      if (_.isEmpty(absenceTypes)) {
        Api.get('HRAbsenceType').then(function (response) {
          if (response.values.length === 0) throw new Error('No absence type not found');

          absenceTypes = response.values;

          deferred.resolve(absenceTypes);
        });
      } else {
        deferred.resolve(absenceTypes);
      }

      return deferred.promise;
    };

    /**
     * @ngdoc method
     * @name getStaffAverage
     * @methodOf LeaveService
     * @returns {*}
     */
    factory.getStaffAverage = function (type) {
      var deferred = $q.defer(), average = 0;

      getCurrentPeriod()
        .then(function (response) {
          if (response.hasOwnProperty('id')) {
            var periodId = response.id;

            Api.post('ContactSummary', {absence_types: type, period_id: periodId}, 'getabsenceaggregate')
              .then(function (response) {
                if (response.values.length === 0) return $q.reject('Staff average not returned');

                var hours = Math.ceil(response.values[0].result / 60);

                average = Math.ceil(hours / 8);

                deferred.resolve(average);
              });
          } else {
            deferred.resolve(average);
          }
        });

      return deferred.promise;
    };

    factory.getDepartmentAverage = function () {
      // todo: need to revisit this once it has been decided which department to show the average for.
    };

    /////////////////////
    // Private Members //
    /////////////////////

    var absenceTypes = [], absences, entitlements, periods;

    function getCurrentPeriod() {
      return getPeriods()
        .then(function (response) {
          var period = {}, now = moment();

          for (var i = 0; i < response.length; i++) {
            var start = moment(response[i].start_date, 'YYYY-MM-DD HH:mm:ss'),
              end = moment(response[i].end_date, 'YYYY-MM-DD HH:mm:ss');

            if (now.diff(start) >= 0 && now.diff(end) <= 0) {
              period = response[i];
            }
          }

          return period;
        });
    }

    function getPreviousPeriod() {
      var currentPeriod, previousPeriod = {};

      return getCurrentPeriod()
        .then(function (response) {
          currentPeriod = response;

          return getPeriods();
        })
        .then(function (response) {
          var currentPeriodIndex = response.indexOf(currentPeriod);

          if (currentPeriodIndex !== -1 && currentPeriodIndex > 0) {
            previousPeriod = response[currentPeriodIndex - 1];
          }

          return previousPeriod;
        });
    }

    function init(periodId) {
      var deferred = $q.defer();

      if (_.isEmpty(factory.collection.getItem(periodId))) {
        factory.getAbsenceTypes()
          .then(function () {
            return factory.getAbsences(periodId);
          })
          .then(function () {
            return factory.getEntitlement(periodId);
          })
          .then(function () {
            return assembleLeave(periodId);
          })
          .then(function () {
            deferred.resolve();
          })
          .catch(function (response) {
            $log.debug('An error has occurred', response);
            deferred.reject(response);
          });
      } else {
        deferred.resolve();
      }

      return deferred.promise;
    }

    function getPeriods() {
      var deferred = $q.defer();

      if (_.isEmpty(periods)) {
        Api.get('HRAbsencePeriod')
          .then(function (response) {
            if (response.values.length === 0) return deferred.reject('No absence periods found');
            periods = response.values;

            periods = $filter('orderBy')(periods, 'start_date');
            console.log('Periods in order', periods);

            deferred.resolve(periods);
          })
          .catch(function (response) {
            $log.debug('An error has occured', response);
            deferred.reject(response);
          });
      } else {
        deferred.resolve(periods);
      }

      return deferred.promise;
    }

    function assembleLeave(periodId) {
      assembleAbsenceTypes(periodId);
      assembleEntitlements(periodId);
      assembleAbsences(periodId);
    }

    function assembleAbsenceTypes(periodId) {
      var data = factory.collection.getItem(periodId) || {};

      angular.forEach(absenceTypes, function (type) {
        if (type.is_active !== '1') return;

        var typeId = type.id;

        if (!data.hasOwnProperty(typeId)) data[typeId] = {};

        data[typeId].type_id = typeId;
        data[typeId].title = type.title;
        data[typeId].credit_activity_type_id = type.credit_activity_type_id ? type.credit_activity_type_id : null;
        data[typeId].debit_activity_type_id = type.debit_activity_type_id ? type.debit_activity_type_id : null;

        // Initialise remaining keys
        data[typeId].entitled = 0;
        data[typeId].taken = 0;
      });

      factory.collection.insertItem(periodId, data);

      //if (_.size(data)) factory.setData(data); // todo
    }

    function assembleEntitlements(periodId) {
      var data = factory.collection.getItem(periodId);

      angular.forEach(entitlements, function (entitlement) {
        var typeId = entitlement.type_id;

        if (!data.hasOwnProperty(typeId)) return;

        data[typeId].entitled = +entitlement.amount;
      });

      factory.collection.insertItem(periodId, data);

      //if (_.size(data)) factory.setData(data); // todo
    }

    function assembleAbsences(periodId) {
      var data = factory.collection.getItem(periodId);

      var absenceActivityTypeLookup = {};
      angular.forEach(absenceTypes, function (type) {
        if (type.credit_activity_type_id) absenceActivityTypeLookup[type.credit_activity_type_id] = type.id;
        if (type.debit_activity_type_id) absenceActivityTypeLookup[type.debit_activity_type_id] = type.id;
      });

      angular.forEach(absences, function (absence) {
        var typeId;

        if (absenceActivityTypeLookup.hasOwnProperty(absence.activity_type_id)) {
          typeId = absenceActivityTypeLookup[absence.activity_type_id];
        }

        if (typeId) {
          if (!data.hasOwnProperty(typeId)) return;

          var hoursTaken = Math.ceil(absence.absence_range.duration / 60);

          data[typeId].taken += Math.ceil(hoursTaken / 8);
        }
      });

      factory.collection.insertItem(periodId, data);

      //if (_.size(data)) factory.setData(data); // todo
    }

    return factory;
  }

  services.factory('LeaveService', ['ApiService', 'ModelService', 'ContactDetailsService', '$q', '$log', '$filter', LeaveService]);
});