(function (CRM) {
  define([
    'common/angular',
    'common/angularMocks',
    'leave-absences/shared/config',
    'leave-absences/manager-leave/app'
  ], function (angular) {
    'use strict';

    describe('managerLeaveContainer', function () {
      var $compile, $log, $rootScope, component, controller;

      beforeEach(module('leave-absences.templates', 'manager-leave'));
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

      it('is contains the expected markup', function () {
        expect(component.find('div.manager-leave-page').length).toBe(1);
      });

      function compileComponent() {
        var $scope = $rootScope.$new();
        var contactId = CRM.vars.leaveAndAbsences.contactId;

        component = angular.element('<manager-leave-container contact-id="' + contactId + '"></manager-leave-container>');
        $compile(component)($scope);
        $scope.$digest();

        controller = component.controller('managerLeaveContainer');
      }
    });
  })
})(CRM);
