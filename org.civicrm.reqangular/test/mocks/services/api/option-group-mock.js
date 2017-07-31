/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/mocks/module'
], function (_, mocks) {
  'use strict';

  mocks.factory('api.optionGroup.mock', ['$q', function ($q) {
    return {
      valuesOf: function (names) {
        var values;

        if (_.isArray(names)) {
          values = _.pick(this.mockedOptionValues(), names);
        } else {
          values = this.mockedOptionValues()[names];
        }

        return promiseResolvedWith(values);
      },

            /**
             * Adds a spy on every method for testing purposes
             */
      spyOnMethods: function () {
        _.functions(this).forEach(function (method) {
          spyOn(this, method).and.callThrough();
        }.bind(this));
      },

            /**
             * Mocked option values, grouped by option group
             */
      mockedOptionValues: function () {
        return {
          hrjc_department: [
            {
              id: '856',
              option_group_id: '112',
              label: 'Finance',
              value: 'Finance',
              weight: '1'
            },
            {
              id: '857',
              option_group_id: '112',
              label: 'HR',
              value: 'HR',
              weight: '2'
            },
            {
              id: '858',
              option_group_id: '112',
              label: 'IT',
              value: 'IT',
              weight: '3'
            },
            {
              id: '859',
              option_group_id: '112',
              label: 'Fundraising',
              value: 'Fundraising',
              weight: '4'
            },
            {
              id: '860',
              option_group_id: '112',
              label: 'Marketing',
              value: 'Marketing',
              weight: '5'
            }
          ],
          hrjc_level_type: [
            {
              id: '845',
              option_group_id: '111',
              label: 'Senior Manager',
              value: 'Senior Manager',
              weight: '1'
            },
            {
              id: '846',
              option_group_id: '111',
              label: 'Junior Manager',
              value: 'Junior Manager',
              weight: '2'
            },
            {
              id: '847',
              option_group_id: '111',
              label: 'Senior Staff',
              value: 'Senior Staff',
              weight: '3'
            },
            {
              id: '848',
              option_group_id: '111',
              label: 'Junior Staff',
              value: 'Junior Staff',
              weight: '4'
            }
          ],
          hrjc_location: [
            {
              id: '854',
              option_group_id: '115',
              label: 'Headquarters',
              value: 'Headquarters',
              weight: '1'
            },
            {
              id: '855',
              option_group_id: '115',
              label: 'Home or Home-Office',
              value: 'Home',
              weight: '1'
            }
          ],
          hrjc_region: [
            {
              id: '1',
              option_group_id: '11',
              label: 'Region #1',
              value: 'Region #1',
              weight: '1'
            },
            {
              id: '2',
              option_group_id: '22',
              label: 'Region #2',
              value: 'Region #2',
              weight: '1'
            }
          ]
        };
      }
    };

        /**
         * Returns a promise that will resolve with the given value
         *
         * @param {any} value
         * @return {Promise}
         */
    function promiseResolvedWith (value) {
      var deferred = $q.defer();
      deferred.resolve(value);

      return deferred.promise;
    }
  }]);
});
