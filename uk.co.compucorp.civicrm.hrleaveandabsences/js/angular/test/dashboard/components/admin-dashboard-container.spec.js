/* eslint-env amd, jasmine */

define([
  'common/angular',
  'leave-absences/admin-dashboard/app'
], function (angular) {
  'use strict';

  describe('adminDashboardContainer', function () {
    var $compile, $log, $rootScope, component, controller;

    beforeEach(module('leave-absences.templates', 'admin-dashboard'));

    beforeEach(inject(function (_$compile_, _$log_, _$rootScope_) {
      $compile = _$compile_;
      $log = _$log_;
      $rootScope = _$rootScope_;

      spyOn($log, 'debug');

      compileComponent();
    }));

    it('has a controller', function () {
      expect(controller).toBeDefined();
    });

    it('is initialized', function () {
      expect($log.debug).toHaveBeenCalled();
    });

    function compileComponent () {
      var $scope = $rootScope.$new();

      component = angular.element('<admin-dashboard-container></admin-dashboard-container>');
      $compile(component)($scope);
      $scope.$digest();

      controller = component.controller('adminDashboardContainer');
    }
  });
});
