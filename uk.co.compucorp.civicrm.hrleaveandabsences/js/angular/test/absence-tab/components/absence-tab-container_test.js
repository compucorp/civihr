(function (CRM) {
  define([
    'common/angular',
    'common/angularMocks',
    'leave-absences/absence-tab/app'
  ], function (angular) {
    'use strict';

    describe('absenceTabContainer', function () {
      var $compile, $httpBackend, $log, $rootScope, component, controller;

      beforeEach(module('leave-absences.templates', 'absence-tab'));
      beforeEach(inject(function (_$compile_, _$httpBackend_, _$log_, _$rootScope_) {
        $compile = _$compile_;
        $httpBackend = _$httpBackend_;
        $log = _$log_;
        $rootScope = _$rootScope_;

        // Catch any request created by the component belonging to
        // first tab enabled by default
        $httpBackend.whenGET(/./).respond(true);

        spyOn($log, 'debug');
        compileComponent();
      }));

      it('is initialized', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      function compileComponent() {
        var $scope = $rootScope.$new();

        component = angular.element('<absence-tab-container></absence-tab-container>');
        $compile(component)($scope);
        $scope.$digest();

        controller = component.controller('absenceTabContainer');
      }
    });
  });
})(CRM);
