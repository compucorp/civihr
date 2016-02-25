define([
    'common/lodash',
    'common/moment',
    'appraisals/modules/models-instances',
    'common/models/instances/instance'
], function (_, moment, instances) {
    'use strict';

    instances.factory('AppraisalInstance', ['ModelInstance', 'HR_settings', function (ModelInstance, HR_settings) {
        var boolFields = ['meeting_completed', 'approved_by_employee', 'is_current'];

        return ModelInstance.extend({

            /**
             * Override of parent method
             *
             * @param {object} result - The accumulator object
             * @param {string} key - The property name
             */
            fromAPIFilter: function (result, __, key) {
                var dateFormat = HR_settings.DATE_FORMAT.toUpperCase();

                if (_.endsWith(key, '_date') || _.endsWith(key, '_due')) {
                    result[key] = moment(this[key], 'YYYY-MM-DD').format(dateFormat);
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
                var dateFormat = HR_settings.DATE_FORMAT.toUpperCase();

                if (_.endsWith(key, '_date') || _.endsWith(key, '_due')) {
                    result[key] = moment(this[key], dateFormat).format('YYYY-MM-DD');
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
