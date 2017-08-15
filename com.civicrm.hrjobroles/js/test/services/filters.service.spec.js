/* eslint-env amd, jasmine */

define([
  'common/angular',
  'mocks/data/job-role.data',
  'common/angularMocks',
  'job-roles/modules/job-roles.module'
], function (angular, Mock) {
  'use strict';

  describe('filtersService', function () {
    var filtersService;
    var notUndefinedArray;
    var costCenters;
    var funders;

    beforeEach(module('hrjobroles'));
    beforeEach(inject(function (_filtersService_) {
      filtersService = _filtersService_;

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
        expect(filtersService.isNotUndefined(notUndefinedArray)).toEqual(expectedArray);
        expect(filtersService.isNotUndefined(notUndefinedArray).length).toBe(3);
      });

      it('should return the passed value if isn\'t an array', function () {
        expect(filtersService.isNotUndefined('test')).toBe('test');
        expect(filtersService.isNotUndefined(null)).toBeNull();
        expect(filtersService.isNotUndefined(true)).toBe(true);
        expect(filtersService.isNotUndefined(undefined)).toBe(undefined);
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
        expect(filtersService.issetCostCentre(costCenters)).toEqual(expectedArray);
        expect(filtersService.issetCostCentre(costCenters).length).toBe(2);
      });

      it('should return the passed value if isn\'t an array', function () {
        expect(filtersService.issetCostCentre('test')).toBe('test');
        expect(filtersService.issetCostCentre(null)).toBeNull();
        expect(filtersService.issetCostCentre(true)).toBe(true);
        expect(filtersService.issetCostCentre(undefined)).toBe(undefined);
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
        expect(filtersService.issetFunder(funders)).toEqual(expectedArray);
        expect(filtersService.issetFunder(funders).length).toBe(2);
      });

      it('should return the passed value if isn\'t an array', function () {
        expect(filtersService.issetFunder('test')).toBe('test');
        expect(filtersService.issetFunder(null)).toBeNull();
        expect(filtersService.issetFunder(true)).toBe(true);
        expect(filtersService.issetFunder(undefined)).toBe(undefined);
      });
    });
  });
});
