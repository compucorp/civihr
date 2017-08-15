/* eslint-env amd, jasmine */

define([
  'common/angular',
  'mocks/data/job-role.data',
  'common/angularMocks',
  'job-roles/modules/job-roles.module'
], function (angular, Mock) {
  'use strict';

  describe('HRJobRolesServiceFilters', function () {
    var HRJobRolesServiceFilters;
    var notUndefinedArray;
    var costCenters;
    var funders;

    beforeEach(module('hrjobroles'));
    beforeEach(inject(function (_HRJobRolesServiceFilters_) {
      HRJobRolesServiceFilters = _HRJobRolesServiceFilters_;

      notUndefinedArray = [1, 2, 'undefined', 'test', undefined];
      costCenters = [
        {
          amount: '0',
          cost_centre_id: '879',
          id: 1,
          percentage: '1',
          type: '1'
        },
        {
          amount: '0',
          cost_centre_id: '890',
          id: 1,
          percentage: '0',
          type: '1'
        },
        {
          amount: '2',
          cost_centre_id: '',
          id: 1,
          percentage: '0',
          type: '0'
        },
        {
          amount: '2',
          cost_centre_id: '123',
          id: 1,
          percentage: '0',
          type: '0'
        }
      ];
      funders = [
        {
          amount: '0',
          funder_id: '',
          id: 2,
          percentage: '2',
          type: '1'
        },
        {
          amount: '0',
          funder_id: {
            id: '1',
            sort_name: 'Default Organization'
          },
          id: 1,
          percentage: '1',
          type: '1'
        },
        {
          amount: '0',
          funder_id: {
            id: '1',
            sort_name: 'Default Organization'
          },
          id: 1,
          percentage: '0',
          type: '1'
        },
        {
          amount: '1',
          funder_id: {
            id: '1',
            sort_name: 'Default Organization'
          },
          id: 1,
          percentage: '0',
          type: '0'
        }
      ];
    }));

    describe('isNotUndefined', function () {
      var expectedArray;

      beforeEach(function () {
        expectedArray = [1, 2, 'test'];
      });

      it('should remove undefined values', function () {
        expect(HRJobRolesServiceFilters.isNotUndefined(notUndefinedArray)).toEqual(expectedArray);
        expect(HRJobRolesServiceFilters.isNotUndefined(notUndefinedArray).length).toBe(3);
      });

      it('should return the passed value if isn\'t an array', function () {
        expect(HRJobRolesServiceFilters.isNotUndefined('test')).toBe('test');
        expect(HRJobRolesServiceFilters.isNotUndefined(null)).toBeNull();
        expect(HRJobRolesServiceFilters.isNotUndefined(true)).toBe(true);
        expect(HRJobRolesServiceFilters.isNotUndefined(undefined)).toBe(undefined);
      });
    });

    describe('issetFunder', function () {
      var expectedArray;

      beforeEach(function () {
        expectedArray = [
          {
            amount: '0',
            cost_centre_id: '879',
            id: 1,
            percentage: '1',
            type: '1'
          },
          {
            amount: '2',
            cost_centre_id: '123',
            id: 1,
            percentage: '0',
            type: '0'
          }
        ];
      });

      it('should remove the entries which are without cost_centre_id', function () {
        expect(HRJobRolesServiceFilters.issetCostCentre(costCenters)).toEqual(expectedArray);
        expect(HRJobRolesServiceFilters.issetCostCentre(costCenters).length).toBe(2);
      });

      it('should return the passed value if isn\'t an array', function () {
        expect(HRJobRolesServiceFilters.issetCostCentre('test')).toBe('test');
        expect(HRJobRolesServiceFilters.issetCostCentre(null)).toBeNull();
        expect(HRJobRolesServiceFilters.issetCostCentre(true)).toBe(true);
        expect(HRJobRolesServiceFilters.issetCostCentre(undefined)).toBe(undefined);
      });
    });

    describe('issetFunder', function () {
      var expectedArray;

      beforeEach(function () {
        expectedArray = [
          {
            amount: '0',
            funder_id: {
              id: '1',
              sort_name: 'Default Organization'
            },
            id: 1,
            percentage: '1',
            type: '1'
          },
          {
            amount: '1',
            funder_id: {
              id: '1',
              sort_name: 'Default Organization'
            },
            id: 1,
            percentage: '0',
            type: '0'
          }
        ];
      });

      it('should remove the entries which are without funder_id', function () {
        expect(HRJobRolesServiceFilters.issetFunder(funders)).toEqual(expectedArray);
        expect(HRJobRolesServiceFilters.issetFunder(funders).length).toBe(2);
      });

      it('should return the passed value if isn\'t an array', function () {
        expect(HRJobRolesServiceFilters.issetFunder('test')).toBe('test');
        expect(HRJobRolesServiceFilters.issetFunder(null)).toBeNull();
        expect(HRJobRolesServiceFilters.issetFunder(true)).toBe(true);
        expect(HRJobRolesServiceFilters.issetFunder(undefined)).toBe(undefined);
      });
    });
  });
});
