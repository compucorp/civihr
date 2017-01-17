define([
    'common/lodash',
    'common/moment',
    'common/modules/models-instances',
    'common/models/instances/instance'
], function (_, moment, instances) {
    'use strict';

    instances.factory('JobRoleInstance', ['ModelInstance', 'HR_settings', function (ModelInstance, HR_settings) {

        return ModelInstance.extend({

            /**
             * Override of parent method
             *
             * @param {object} result - The accumulator object
             * @param {string} key - The property name
             */
            fromAPIFilter: function (result, __, key) {
                var dateFormat = HR_settings.DATE_FORMAT ? HR_settings.DATE_FORMAT.toUpperCase() : 'YYYY-MM-DD';

                if (_.endsWith(key, '_date')) {
                    result[key] = moment(this[key], 'YYYY-MM-DD HH:mm:ss').format(dateFormat);
                } else if (key === 'api.HRJobContract.getsingle') {
                    result.contact_id = this[key].contact_id;
                    result.job_contract_id = this[key].id;
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
                var blackList = ['contact_id', 'job_contract_id'];
                var dateFormat = HR_settings.DATE_FORMAT.toUpperCase();

                if (_.endsWith(key, '_date')) {
                    result[key] = moment(this[key], dateFormat).format('YYYY-MM-DD HH:mm:ss');
                } else if (_.includes(blackList, key)) {
                    return;
                } else {
                    result[key] = this[key];
                }
            }
        });
    }]);
});
