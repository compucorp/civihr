/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'common/modules/models-instances',
  'common/models/instances/instance'
], function (_, moment, instances) {
  'use strict';

  instances.factory('JobRoleInstance', ['ModelInstance', 'HR_settings',
    function (ModelInstance, HRSettings) {
      return ModelInstance.extend({

        /**
         * Override of parent method
         *
         * @param {Object} result - The accumulator object
         * @param {String} key - The property name
         */
        fromAPIFilter: function (result, __, key) {
          var dateFormat = (HRSettings.DATE_FORMAT || 'YYYY-MM-DD').toUpperCase();

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
         * @param {Object} result - The accumulator object
         * @param {String} key - The property name
         */
        toAPIFilter: function (result, __, key) {
          var blackList = ['contact_id', 'job_contract_id'];
          var dateFormat = (HRSettings.DATE_FORMAT || 'YYYY-MM-DD').toUpperCase();

          if (_.endsWith(key, '_date')) {
            result[key] = moment(this[key], dateFormat).format('YYYY-MM-DD HH:mm:ss');
          } else if (!_.includes(blackList, key)) {
            result[key] = this[key];
          }
        }
      });
    }]);
});
