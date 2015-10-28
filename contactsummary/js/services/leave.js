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
  function LeaveService(Api, Model, ContactDetails, $q, $log) {
    $log.debug('Service: LeaveService');

    ////////////////////
    // Public Members //
    ////////////////////

    /**
     * @ngdoc service
     * @name LeaveService
     */
    var factory = Model.createInstance();

    /**
     * @ngdoc method
     * @name get
     * @methodOf LeaveService
     * @returns {*}
     */
    factory.get = function () {
      /** @type {(LeaveService|ModelService)} */
      var self = this;

      return init().then(function () {
        return self.getData();
      });
    };

    /**
     * @ngdoc method
     * @name getEntitlement
     * @methodOf LeaveService
     */
    factory.getEntitlement = function () {
      var deferred = $q.defer();

      if (_.isEmpty(entitlements)) {
        ContactDetails.get()
          .then(function (response) {
            return Api.get('HRAbsenceEntitlement', {contact_id: response.id, options: {'absence-range': 1}});
          })
          .then(function (response) {
            if (response.values.length === 0) return deferred.reject('No absence entitlement found');

            entitlements = response.values;

            deferred.resolve(entitlements);
          });
      } else {
        deferred.resolve(entitlements);
      }

      return deferred.promise;
    };

    /**
     * @ngdoc method
     * @name getAbsences
     * @methodOf LeaveService
     * @returns {*}
     */
    factory.getAbsences = function () {
      var deferred = $q.defer();

      if (_.isEmpty(absences)) {
        ContactDetails.get()
          .then(function (response) {
            var data = {
              target_contact_id: response.id,
              period_id: [1], // todo: make this dynamic
              options: {'absence-range': 1},
              sequential: 0 // this is important in order to get absences in correct format!
            };

            return Api.post('Activity', data, 'getabsences');
          })
          .then(function (response) {
            if (response.values.length === 0) return deferred.reject('No absences found');

            absences = response.values;

            deferred.resolve(absences);
          });
      } else {
        deferred.resolve(absences);
      }

      return deferred.promise;
    };

    /**
     * @ngdoc method
     * @name getAbsenceTypes
     * @methodOf LeaveService
     */
    factory.getAbsenceTypes = function () {
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

    /////////////////////
    // Private Members //
    /////////////////////

    var absenceTypes = [], absences, entitlements;

    function init() {
      var deferred = $q.defer();

      if (_.isEmpty(factory.getData())) {
        factory.getAbsenceTypes()
          .then(factory.getAbsences)
          .then(factory.getEntitlement)
          .then(assembleLeave)
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

    function assembleLeave() {
      assembleAbsenceTypes();
      assembleEntitlements();
      assembleAbsences();
    }

    function assembleAbsenceTypes() {
      var data = factory.getData();

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

      if (_.size(data)) factory.setData(data);
    }

    function assembleEntitlements() {
      var data = factory.getData();

      angular.forEach(entitlements, function (entitlement) {
        var typeId = entitlement.type_id;

        if (!data.hasOwnProperty(typeId)) return;

        data[typeId].entitled = +entitlement.amount;
      });

      if (_.size(data)) factory.setData(data);
    }

    function assembleAbsences() {
      var data = factory.getData();

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

      if (_.size(data)) factory.setData(data);
    }

    return factory;
  }

  services.factory('LeaveService', ['ApiService', 'ModelService', 'ContactDetailsService', '$q', '$log', LeaveService]);
});