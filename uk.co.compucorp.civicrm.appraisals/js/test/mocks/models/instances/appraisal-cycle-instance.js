define([
    'common/angular',
    'common/lodash',
    'mocks/module'
], function (angular, _, mocks) {
    'use strict';

    mocks.factory('AppraisalCycleInstanceMock', ['AppraisalCycleInstance', function (instance) {

        return angular.extend({}, instance, {

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
    }]);
});
