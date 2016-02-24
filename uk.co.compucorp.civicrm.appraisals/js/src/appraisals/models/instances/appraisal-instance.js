define([
    'common/lodash',
    'common/moment',
    'appraisals/modules/models-instances',
    'common/models/instances/instance'
], function (_, moment, instances) {
    'use strict';

    instances.factory('AppraisalInstance', ['ModelInstance', function (ModelInstance) {
        var boolFields = ['meeting_completed', 'approved_by_employee', 'is_current'];

        return ModelInstance.extend({

            /**
             * Override of parent method
             *
             * @param {object} result - The accumulator object
             * @param {string} key - The property name
             */
            fromAPIFilter: function (result, __, key) {
                if (_.endsWith(key, '_date') || _.endsWith(key, '_due')) {
                    result[key] = moment(this[key], 'YYYY-MM-DD').format('DD/MM/YYYY');
                } else if (_.includes(boolFields, key)) {
                    result[key] = !!(+this[key]);
                } else {
                    result[key] = this[key];
                }
            },

            /**
             * Override of parent method
             *
             * @param {object} result - The accumulator object
             * @param {string} key - The property name
             */
            toAPIFilter: function (result, __, key) {
                if (_.endsWith(key, '_date') || _.endsWith(key, '_due')) {
                    result[key] = moment(this[key], 'DD/MM/YYYY').format('YYYY-MM-DD');
                } else if (_.includes(boolFields, key)) {
                    // (true, false) -> ('1', '0')
                    result[key] = '' + +this[key];
                } else {
                    result[key] = this[key];
                }
            }
        });
    }]);
});
