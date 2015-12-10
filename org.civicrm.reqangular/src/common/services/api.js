define([
    'common/angular',
    'common/modules/apis'
], function (angular, apis) {
    'use strict';

    apis.factory('api', ['$log', '$http', '$q', function ($log, $http, $q) {
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
             * # TO DO #
             */
            mockGET: function (result) {
                var deferred = $q.defer();
                deferred.resolve(result);

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
