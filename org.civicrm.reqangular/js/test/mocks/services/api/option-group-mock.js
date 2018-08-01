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
              id: '1',
              option_group_id: '1',
              is_reserved: '1',
              is_active: '1',
              label: 'Finance',
              name: 'finance',
              option_group_name: 'hrjc_department',
              value: '1',
              weight: '1'
            },
            {
              id: '2',
              option_group_id: '1',
              is_reserved: '1',
              is_active: '1',
              label: 'HR',
              name: 'hr',
              option_group_name: 'hrjc_department',
              value: '2',
              weight: '2'
            },
            {
              id: '3',
              option_group_id: '1',
              is_reserved: '1',
              is_active: '1',
              label: 'IT',
              name: 'it',
              option_group_name: 'hrjc_department',
              value: '3',
              weight: '3'
            },
            {
              id: '4',
              option_group_id: '1',
              is_reserved: '1',
              is_active: '1',
              label: 'Fundraising',
              name: 'fundraising',
              option_group_name: 'hrjc_department',
              value: '4',
              weight: '4'
            },
            {
              id: '5',
              option_group_id: '1',
              is_reserved: '1',
              is_active: '1',
              label: 'Marketing',
              name: 'marketing',
              option_group_name: 'hrjc_department',
              value: '5',
              weight: '5'
            }
          ],
          hrjc_level_type: [
            {
              id: '6',
              option_group_id: '2',
              is_reserved: '1',
              is_active: '1',
              label: 'Senior Manager',
              name: 'seniormanager',
              option_group_name: 'hrjc_level_type',
              value: '1',
              weight: '1'
            },
            {
              id: '7',
              option_group_id: '2',
              is_reserved: '1',
              is_active: '1',
              label: 'Junior Manager',
              name: 'juniormanager',
              option_group_name: 'hrjc_level_type',
              value: '2',
              weight: '2'
            },
            {
              id: '8',
              option_group_id: '2',
              is_reserved: '1',
              is_active: '1',
              label: 'Senior Staff',
              name: 'seniorstaff',
              option_group_name: 'hrjc_level_type',
              value: '3',
              weight: '3'
            },
            {
              id: '9',
              option_group_id: '2',
              is_reserved: '1',
              is_active: '1',
              label: 'Junior Manager',
              name: 'juniorstaff',
              option_group_name: 'hrjc_level_type',
              value: '4',
              weight: '4'
            }
          ],
          hrjc_location: [
            {
              id: '10',
              option_group_id: '3',
              is_reserved: '1',
              is_active: '1',
              label: 'Headquarters',
              name: 'headquarters',
              option_group_name: 'hrjc_location',
              value: '1',
              weight: '1'
            },
            {
              id: '11',
              option_group_id: '3',
              is_reserved: '1',
              is_active: '1',
              label: 'Home or Home-Office',
              name: 'home',
              option_group_name: 'hrjc_location',
              value: '2',
              weight: '2'
            }
          ],
          hrjc_region: [
            {
              id: '12',
              option_group_id: '4',
              is_reserved: '1',
              is_active: '1',
              label: 'Region #1',
              name: 'region1',
              option_group_name: 'hrjc_region',
              value: '1',
              weight: '1'
            },
            {
              id: '13',
              option_group_id: '4',
              is_reserved: '1',
              is_active: '1',
              label: 'Region #2',
              name: 'region2',
              option_group_name: 'hrjc_region',
              value: '2',
              weight: '2'
            }
          ],
          hrleaveandabsences_absence_type_calculation_unit: [
            {
              id: '14',
              option_group_id: '5',
              is_reserved: '1',
              is_active: '1',
              label: 'Days',
              name: 'days',
              option_group_name: 'hrleaveandabsences_absence_type_calculation_unit',
              value: '1',
              weight: '1'
            },
            {
              id: '15',
              option_group_id: '5',
              is_reserved: '1',
              is_active: '1',
              label: 'Hours',
              name: 'hours',
              option_group_name: 'hrleaveandabsences_absence_type_calculation_unit',
              value: '2',
              weight: '2'
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
