(function (CRM) {
  define([
    'common/angular',
    'common/angularMocks',
    'leave-absences/shared/config',
    'leave-absences/absence-tab/app'
  ], function (angular) {
    'use strict';

    describe('absenceTab', function () {
      var $compile, $log, $rootScope, component, controller;

      beforeEach(module('leave-absences.templates', 'absence-tab'));
      beforeEach(inject(function (_$compile_, _$log_, _$rootScope_) {
        $compile = _$compile_;
        $log = _$log_;
        $rootScope = _$rootScope_;

        spyOn($log, 'debug');

        compileComponent();
      }));

      it('is initialized', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      function compileComponent() {
        var $scope = $rootScope.$new();

        component = angular.element('<absence-tab></absence-tab>');
        $compile(component)($scope);
        $scope.$digest();

        controller = component.controller('absenceTab');
      }
    });
  });
})(CRM);
