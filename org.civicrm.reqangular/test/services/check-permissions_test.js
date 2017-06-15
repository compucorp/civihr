/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'common/angular',
    'common/angularMocks',
    'common/services/check-permissions'
  ], function () {
    'use strict';

    describe('checkPermissions', function () {
      var $rootScope, checkPermissions, promise;
      var ownedPermissions = ['permission #1', 'permission #2'];

      beforeEach(module('common.services'));
      beforeEach(inject(function (_$rootScope_, _checkPermissions_) {
        $rootScope = _$rootScope_;
        checkPermissions = _checkPermissions_;

        spyOn(CRM, 'checkPerm').and.callFake(function (permission) {
          return ownedPermissions.indexOf(permission) !== -1;
        });
      }));

      afterEach(function () {
        $rootScope.$digest();
      });

      describe('basic tests', function () {
        var permissionToCheck = 'permission #1';

        beforeEach(function () {
          promise = checkPermissions(permissionToCheck);
        });

        it('returns a promise', function () {
          expect(promise).toEqual(jasmine.objectContaining({
            then: jasmine.any(Function),
            catch: jasmine.any(Function),
            finally: jasmine.any(Function)
          }));
        });

        it('utilizes the the `CRM.checkPerm` method', function () {
          expect(CRM.checkPerm).toHaveBeenCalledWith(permissionToCheck);
        });

        it('checks if the currently logged in has the given permission', function () {
          promise.then(function (outcome) {
            expect(outcome).toBe(true);
          });
        });
      });

      describe('when given multiple permissions', function () {
        describe('when the user has all the permissions', function () {
          var permissionsToCheck = ['permission #1', 'permission #2'];

          it('returns true', function () {
            checkPermissions(permissionsToCheck).then(function (outcome) {
              expect(outcome).toBe(true);
            });
          });
        });

        describe('when the user does not have all the permissions', function () {
          var permissionsToCheck = ['permission #1', 'permission #2', 'permission #3'];

          it('returns false', function () {
            checkPermissions(permissionsToCheck).then(function (outcome) {
              expect(outcome).toBe(false);
            });
          });
        });
      });
    });
  });
})(CRM);
