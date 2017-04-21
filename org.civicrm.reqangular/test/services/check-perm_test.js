(function (CRM) {
  define([
    'common/angular',
    'common/angularMocks',
    'common/services/check-perm'
  ], function () {
    'use strict';

    describe('CheckPermissions', function () {
      var $q, $rootScope, checkPermissions;

      beforeEach(module('common.services'));

      beforeEach(inject(function (_$rootScope_, _$q_, _CheckPermissions_) {
        $rootScope = _$rootScope_;
        $q = _$q_;
        checkPermissions = _CheckPermissions_;
      }));

      describe('canAdmin', function () {
        var promise, adminPermission;

        beforeEach(function () {
          adminPermission = 'CiviHRLeaveAndAbsences: Administer Leave and Absences';
          spyOn(CRM, 'checkPerm').and.callFake(returnTrueIfPermissionIs(adminPermission));

          promise = checkPermissions.canAdmin();
        });

        afterEach(function () {
          $rootScope.$apply();
        });

        it('calls CRM api with expected parameter', function () {
          promise.then(function () {
            expect(CRM.checkPerm).toHaveBeenCalledWith(adminPermission);
          });
        });

        it('returns true', function () {
          promise.then(function (result) {
            expect(result).toBeTruthy();
          });
        });
      });

      /**
       * Helper to return true for given permission
       *
       * @param {String} permission to check
       * @return {Function} a fake implementation for checking for permission
       */
      function returnTrueIfPermissionIs(permissionToCheck) {
        return function(permission) {
          return (permission === permissionToCheck);
        };
      }
    });
  });
})(CRM);
