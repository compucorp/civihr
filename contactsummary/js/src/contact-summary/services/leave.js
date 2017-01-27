/* eslint valid-jsdoc: 0, "require-jsdoc": 0, no-implicit-coercion: 0 */
define([
  'common/lodash',
  'contact-summary/modules/services',
  'common/moment',
  'contact-summary/services/api',
  'contact-summary/services/model',
  'contact-summary/services/contactDetails'
], function(_, services, moment) {
  'use strict';

  var promiseCache = {};

  /**
   * @param {ApiService} Api
   * @param {ModelService} Model
   * @param {ContactDetailsService} ContactDetails
   * @param $q
   * @param $log
   * @returns {ModelService|Object|*}
   * @constructor
   */
  function LeaveService($q, $log, $filter, Api, Model, ContactDetails) {
    $log.debug('Service: LeaveService');

    // /////////////////
    // Public Members //
    // /////////////////

    /**
     * @ngdoc service
     * @name LeaveService
     */
    var factory = {};

    factory.collection = {
      items: {},
      insertItem: function(key, item) {
        this.items[key] = item;
      },
      getItem: function(key) {
        return this.items[key];
      },
      set: function(collection) {
        this.items = collection;
      },
      get: function() {
        return this.items;
      }
    };

    factory.getCollection = function() {
      return this.collection.get();
    };

    factory.getCurrentPeriod = function() {
      return getPeriods()
        .then(function(response) {
          var period = {};
          var now = moment();

          for (var i = 0; i < response.length; i++) {
            var start = moment(response[i].start_date, 'YYYY-MM-DD HH:mm:ss');
            var end = moment(response[i].end_date, 'YYYY-MM-DD HH:mm:ss');

            if (now.diff(start) >= 0 && now.diff(end) <= 0) {
              period = response[i];
            }
          }

          return period;
        });
    };

    /**
     * @ngdoc method
     * @name get
     * @methodOf LeaveService
     * @returns {*}
     */
    factory.get = function() {
      /** @type {(LeaveService|ModelService)} */
      var self = this;
      var periodId;

      return init(periodId).then(function() {
        return self.getData();
      });
    };

    factory.getCurrent = function() {
      /** @type {(LeaveService|ModelService)} */
      var self = this;
      var deferred = $q.defer();
      var periodId;

      if (!promiseCache.getCurrent) {
        factory.getCurrentPeriod()
          .then(function (response) {
            if (response.hasOwnProperty('id')) {
              periodId = response.id;

              init(periodId).then(function () {
                deferred.resolve(self.collection.getItem(periodId));
              });
            } else {
              deferred.resolve({});
            }
          });

        promiseCache.getCurrent = deferred.promise;
      }

      return promiseCache.getCurrent;
    };

    factory.getPrevious = function() {
      /** @type {(LeaveService|ModelService)} */
      var self = this;
      var deferred = $q.defer();
      var periodId;

      getPreviousPeriod()
        .then(function(response) {
          if (response.hasOwnProperty('id')) {
            periodId = response.id;

            init(periodId).then(function() {
              deferred.resolve(self.collection.getItem(periodId));
            });
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
    factory.getEntitlement = function(periodId) {
      var deferred = $q.defer();

      ContactDetails.get()
        .then(function(response) {
          var data = {
            contact_id: response.id,
            period_id: periodId,
            options: {
              'absence-range': 1
            }
          };

          return Api.get('HRAbsenceEntitlement', data);
        })
        .then(function(response) {
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
    factory.getAbsences = function(periodId) {
      var deferred = $q.defer();

      ContactDetails.get()
        .then(function(response) {
          var data = {
            target_contact_id: response.id,
            period_id: [periodId],
            options: {
              'absence-range': 1
            },
            sequential: 0 // this is *important* in order to get absences in correct format!
          };

          return Api.post('Activity', data, 'getabsences');
        })
        .then(function(response) {
          absences = _.filter(response.values, function(absence) {
            return absence.status_id === '2';
          });

          deferred.resolve(absences);
        });

      return deferred.promise;
    };

    /**
     * @ngdoc method
     * @name getAbsenceTypes
     * @methodOf LeaveService
     */
    factory.getAbsenceTypes = function() {
      var deferred = $q.defer();

      if (_.isEmpty(absenceTypes)) {
        Api.get('HRAbsenceType').then(function(response) {
          if (response.values.length === 0) {
            throw new Error('No absence type not found');
          }

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
    factory.getStaffAverage = function(type) {
      var deferred = $q.defer();
      var days = 0;

      factory.getCurrentPeriod()
        .then(function(response) {
          if (response.hasOwnProperty('id')) {
            var periodId = response.id;

            Api.post('ContactSummary', {
              absence_types: type,
              period_id: periodId
            }, 'getabsenceaggregate')
              .then(function(response) {
                if (response.values.length === 0) {
                  return $q.reject('Staff average not returned');
                }

                var hours = Math.ceil(response.values[0].result / 60),
                  days = +(hours / 8).toFixed(1);

                deferred.resolve(days);
              });
          } else {
            deferred.resolve(days);
          }
        });

      return deferred.promise;
    };

    factory.getDepartmentAverage = function() {
      // todo: need to revisit this once it has been decided which department to show the average for.
    };

    // ///////////////////
    // Private Members //
    // ///////////////////

    var absenceTypes = [];
    var absences;
    var entitlements;
    var periods;

    function getPreviousPeriod() {
      var currentPeriod;
      var previousPeriod = {};

      return factory.getCurrentPeriod()
        .then(function(response) {
          currentPeriod = response;

          return getPeriods();
        })
        .then(function(response) {
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
          .then(function() {
            return factory.getAbsences(periodId);
          })
          .then(function() {
            return factory.getEntitlement(periodId);
          })
          .then(function() {
            return assembleLeave(periodId);
          })
          .then(function() {
            deferred.resolve();
          })
          .catch(function(response) {
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
          .then(function(response) {
            if (response.values.length === 0) {
              return deferred.reject('No absence periods found');
            }

            periods = response.values;
            periods = $filter('orderBy')(periods, 'start_date');

            deferred.resolve(periods);
          })
          .catch(function(response) {
            $log.debug('An error has occurred', response);
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

      angular.forEach(absenceTypes, function(type) {
        if (type.is_active !== '1') {
          return;
        }

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

      // if (_.size(data)) factory.setData(data); // todo
    }

    function assembleEntitlements(periodId) {
      var data = factory.collection.getItem(periodId);

      angular.forEach(entitlements, function(entitlement) {
        var typeId = entitlement.type_id;

        if (!data.hasOwnProperty(typeId)) {
          return;
        }

        // Because we don't want to show entitlements for TOIL - they will only include
        // accrued TOIL days.
        if (data[typeId].title.toLowerCase() !== 'toil') {
          data[typeId].entitled = +entitlement.amount;
        }
      });

      factory.collection.insertItem(periodId, data);

      // if (_.size(data)) factory.setData(data); // todo
    }

    function assembleAbsences(periodId) {
      var data = factory.collection.getItem(periodId);
      var absenceActivityTypeLookup = {};

      angular.forEach(absenceTypes, function(type) {
        if (type.credit_activity_type_id) {
          absenceActivityTypeLookup[type.credit_activity_type_id] = type.id;
        }

        if (type.debit_activity_type_id) {
          absenceActivityTypeLookup[type.debit_activity_type_id] = type.id;
        }
      });

      angular.forEach(absences, function(absence) {
        var typeId;

        if (absenceActivityTypeLookup.hasOwnProperty(absence.activity_type_id)) {
          typeId = absenceActivityTypeLookup[absence.activity_type_id];
        }

        if (typeId) {
          if (!data.hasOwnProperty(typeId)) return;

          var hours = Math.ceil(absence.absence_range.approved_duration / 60),
            days = +(hours / 8).toFixed(1);

          if (data[typeId].title.toLowerCase() === 'toil') {
            if (absence.activity_type_id === data[typeId].credit_activity_type_id) {
              data[typeId].entitled += days;
            } else {
              data[typeId].taken += days;
            }
          } else {
            data[typeId].taken += days;
          }
        }
      });

      factory.collection.insertItem(periodId, data);

      // if (_.size(data)) factory.setData(data); // todo
    }

    return factory;
  }

  services.factory('LeaveService', ['$q', '$log', '$filter', 'ApiService', 'ModelService', 'ContactDetailsService', LeaveService]);
});
