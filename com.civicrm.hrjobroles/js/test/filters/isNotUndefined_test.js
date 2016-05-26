define([
  'common/angular',
  'job-roles/app'
], function (angular) {
  'use strict';

  describe('isNotUndefined', function () {
    var isNotUndefined;

    beforeEach(module('hrjobroles'));
    beforeEach(inject(function (isNotUndefinedFilter) {
      isNotUndefined = isNotUndefinedFilter;
    }));

    it('should remove undefined values', function () {
      var array = [1, 2, 'undefined', 'test', undefined];

      expect(isNotUndefined(array).length).toBe(3);
    });

    it('should return the passed value if isn\'t an array', function () {
      expect(isNotUndefined('test')).toBe('test');
      expect(isNotUndefined(null)).toBeNull();
      expect(isNotUndefined(true)).toBe(true);
      expect(isNotUndefined(undefined)).toBe(undefined);
    });
  });
});
