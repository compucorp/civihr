define([
    'common/angular',
    'common/modules/apis'
], function (angular, apis) {
    'use strict';

    apis.factory('api', ['$log', '$http', '$q', '$timeout', function ($log, $http, $q, $timeout) {
        $log.debug('api');

        // Draft

        return {

            /**
             * Extends the api with the given child api
             *
             * @param {object} childAPI
             * @return {object} the child api with the basic api as prototype
             */
            extend: function (childAPI) {
                return angular.extend(Object.create(this), childAPI);
            },

            /**
             * Mocks a GET request to the backend endpoints
             *
             * @param {any} result - The result the mocked request must return
             * @param {int} timeout - The value of a simulated delay in ms
             * @return {Promise}
             */
            mockGET: function (result, delay) {
                var deferred = $q.defer();

                $timeout(function() {
                  deferred.resolve(result);
                }, delay || 0);

                return deferred.promise;
            },

            /**
             * # TO DO #
             */
            sendGET: function () {
                $log.debug('sendGET');
            },

            /**
             * # TO DO #
             */
            sendPOST: function () {
                $log.debug('sendPOST');
            }
        };
    }]);
});
