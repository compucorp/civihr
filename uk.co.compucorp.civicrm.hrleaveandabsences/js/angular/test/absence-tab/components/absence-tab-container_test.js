/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'common/angular',
    'common/angularMocks',
    'leave-absences/absence-tab/app'
  ], function (angular) {
    'use strict';

    describe('absenceTabContainer', function () {
      var $componentController, $log, $rootScope;

      beforeEach(module('absence-tab'));
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
        $componentController('absenceTabContainer');
        $rootScope.$digest();
      }
    });
  });
})(CRM);
