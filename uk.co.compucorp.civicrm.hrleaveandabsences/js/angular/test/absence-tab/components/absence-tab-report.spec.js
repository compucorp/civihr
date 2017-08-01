/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'common/angular',
    'common/angularMocks',
    'leave-absences/absence-tab/app'
  ], function (angular) {
    'use strict';

    describe('absenceTabReport', function () {
      var $componentController, $log, $rootScope;

      beforeEach(module('leave-absences.templates', 'absence-tab'));
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
        $componentController('absenceTabReport', null, { contactId: CRM.vars.leaveAndAbsences.contactId });
        $rootScope.$digest();
      }
    });
  });
})(CRM);
