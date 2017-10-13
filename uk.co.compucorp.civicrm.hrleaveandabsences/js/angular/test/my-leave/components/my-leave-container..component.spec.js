/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'common/angularMocks',
    'leave-absences/shared/config',
    'leave-absences/my-leave/app'
  ], function () {
    'use strict';

    describe('myLeaveContainer', function () {
      var $componentController, $log, $rootScope;

      beforeEach(module('leave-absences.templates', 'my-leave'));
      beforeEach(inject(function (_$componentController_, _$log_, _$rootScope_) {
        $componentController = _$componentController_;
        $log = _$log_;
        $rootScope = _$rootScope_;
        spyOn($log, 'debug');

        compileComponent();
      }));

      it('is initialized', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      function compileComponent () {
        $componentController('myLeaveContainer', null, { contactId: CRM.vars.leaveAndAbsences.contactId });
        $rootScope.$digest();
      }
    });
  });
})(CRM);
