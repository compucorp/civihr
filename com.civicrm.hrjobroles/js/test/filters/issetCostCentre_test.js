define([
  'common/angular',
  'mocks/job-roles',
  'common/angularMocks',
  'job-roles/app'
], function (angular, Mock) {
  'use strict';

  describe('issetFunder', function () {
    var issetCostCentre;

    beforeEach(module('hrjobroles'));
    beforeEach(inject(function (issetCostCentreFilter) {
      issetCostCentre = issetCostCentreFilter;
    }));

    it('should remove the entries which are without cost_centre_id', function () {
      var cost_centers = angular.copy(Mock.roles_data[3]['cost_centers']);

      expect(issetCostCentre(cost_centers).length).toBe(2);
    });

    it('should return the passed value if isn\'t an array', function () {
      expect(issetCostCentre('test')).toBe('test');
      expect(issetCostCentre(null)).toBeNull();
      expect(issetCostCentre(true)).toBe(true);
      expect(issetCostCentre(undefined)).toBe(undefined);
    });
  });
});
