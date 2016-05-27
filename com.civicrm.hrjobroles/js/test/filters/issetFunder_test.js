define([
  'common/angular',
  'mocks/job-roles',
  'common/angularMocks',
  'job-roles/app'
], function (angular, Mock) {
  'use strict';

  describe('issetFunder', function () {
    var issetFunder;

    beforeEach(module('hrjobroles'));
    beforeEach(inject(function (issetFunderFilter) {
      issetFunder = issetFunderFilter;
    }));

    it('should remove the entries which are without funder_id', function () {
      var funders = angular.copy(Mock.roles_data[3]['funders']);

      expect(issetFunder(funders).length).toBe(2);
    });

    it('should return the passed value if isn\'t an array', function () {
      expect(issetFunder('test')).toBe('test');
      expect(issetFunder(null)).toBeNull();
      expect(issetFunder(true)).toBe(true);
      expect(issetFunder(undefined)).toBe(undefined);
    });
  });
});
