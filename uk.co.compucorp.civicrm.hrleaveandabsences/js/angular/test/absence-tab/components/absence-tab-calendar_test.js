(function (CRM) {
  define([
    'common/angular',
    'common/angularMocks',
    'leave-absences/absence-tab/app'
  ], function (angular) {
    'use strict';

    describe('absenceTabCalendar', function () {
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
        var contactId = CRM.vars.leaveAndAbsences.contactId;

        component = angular.element('<absence-tab-calendar contact-id="' + contactId + '"></absence-tab-calendar>');
        $compile(component)($scope);
        $scope.$digest();

        controller = component.controller('absenceTabCalendar');
      }
    });
  });
})(CRM);
