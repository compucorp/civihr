define([
  'common/angular',
  'mocks/job-roles',
  'common/angularMocks',
  'job-roles/app'
], function (angular, Mock) {
  'use strict';

  describe('HRJobRolesServiceFilters', function () {
    var HRJobRolesServiceFilters;

    beforeEach(module('hrjobroles'));
    beforeEach(inject(function (_HRJobRolesServiceFilters_) {
      HRJobRolesServiceFilters = _HRJobRolesServiceFilters_;
    }));

    describe('isNotUndefined', function () {
      it('should remove undefined values', function () {
        var array = [1, 2, 'undefined', 'test', undefined];
        var expectedArray = [1, 2, 'test'];

        expect(HRJobRolesServiceFilters.isNotUndefined(array)).toEqual(expectedArray);
        expect(HRJobRolesServiceFilters.isNotUndefined(array).length).toBe(3);
      });

      it('should return the passed value if isn\'t an array', function () {
        expect(HRJobRolesServiceFilters.isNotUndefined('test')).toBe('test');
        expect(HRJobRolesServiceFilters.isNotUndefined(null)).toBeNull();
        expect(HRJobRolesServiceFilters.isNotUndefined(true)).toBe(true);
        expect(HRJobRolesServiceFilters.isNotUndefined(undefined)).toBe(undefined);
      });
    });

    describe('issetFunder', function () {
      it('should remove the entries which are without cost_centre_id', function () {
        var cost_centers = angular.copy(Mock.roles_data[3]['cost_centers']);
        var expectedArray = [
          {
            amount:"0",
            cost_centre_id:"879",
            id:1,
            percentage:"1",
            type:"1",
          },
          {
            amount:"2",
            cost_centre_id:"123",
            id:1,
            percentage:"0",
            type:"0",
          }
        ];

        expect(HRJobRolesServiceFilters.issetCostCentre(cost_centers)).toEqual(expectedArray);
        expect(HRJobRolesServiceFilters.issetCostCentre(cost_centers).length).toBe(2);
      });

      it('should return the passed value if isn\'t an array', function () {
        expect(HRJobRolesServiceFilters.issetCostCentre('test')).toBe('test');
        expect(HRJobRolesServiceFilters.issetCostCentre(null)).toBeNull();
        expect(HRJobRolesServiceFilters.issetCostCentre(true)).toBe(true);
        expect(HRJobRolesServiceFilters.issetCostCentre(undefined)).toBe(undefined);
      });
    });

    describe('issetFunder', function () {
      it('should remove the entries which are without funder_id', function () {
        var funders = angular.copy(Mock.roles_data[3]['funders']);
        var expectedArray = [
          {
            amount: "0",
            funder_id: {
              id:"1",
              sort_name:"Default Organization"
            },
            id: 1,
            percentage: "1",
            type: "1"
          },
          {
            amount: "1",
            funder_id: {
              id:"1",
              sort_name:"Default Organization"
            },
            id: 1,
            percentage: "0",
            type: "0"
          }
        ];

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
