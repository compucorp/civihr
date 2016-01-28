define([
    'common/angular',
    'common/lodash',
    'mocks/module'
], function (angular, _, mocks) {
    'use strict';

    mocks.factory('AppraisalCycleInstanceMock', ['$q', 'AppraisalCycleInstance', function ($q, instance) {

        return angular.extend({}, instance, {

            update: jasmine.createSpy('update').and.callFake(function (value) {
                return promiseResolvedWith(instance.toAPI());
            }),

            /**
             * Checks if the given object is a modal instance
             *
             * @param {object} object
             * @return {boolean}
             */
            isInstance: function (object) {
                return _.isEqual(_.functions(object), _.functions(instance));
            }
        });


        /**
         * Returns a promise that will resolve with the given value
         *
         * @param {any} value
         * @return {Promise}
         */
        function promiseResolvedWith(value) {
            var deferred = $q.defer();
            deferred.resolve(value);

            return deferred.promise;
        }
    }]);
});
