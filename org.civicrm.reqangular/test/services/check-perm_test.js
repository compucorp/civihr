(function (CRM) {
  define([
    'common/angular',
    'common/angularMocks',
    'common/services/check-perm'
  ], function () {
    'use strict';

    describe('CheckPermissions', function () {
      var checkPermissions;

      beforeEach(module('common.services'));

      beforeEach(inject(function (_CheckPermissions_) {
        checkPermissions = _CheckPermissions_;
      }));

      describe('canAdmin', function () {
        var isAdmin;

        beforeEach(function () {
          spyOn(CRM, 'checkPerm').and.returnValue(true);
          isAdmin = checkPermissions.canAdmin();
        });

        it('calls CRM api with expected parameter', function () {
          expect(CRM.checkPerm).toHaveBeenCalledWith('CiviHRLeaveAndAbsences: Administer Leave and Absences');
        });

        it('returns true', function () {
          expect(isAdmin).toBeTruthy();
        });
      });
    });
  });
})(CRM);
