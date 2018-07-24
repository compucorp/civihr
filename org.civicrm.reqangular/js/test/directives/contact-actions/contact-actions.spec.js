/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/angularMocks',
  'common/directives/contact-actions/contact-actions'
], function (angular) {
  'use strict';

  describe('contactActions directive', function () {
    var $scope, buildDirective;

    beforeEach(module('common.directives'));
    beforeEach(inject(function ($rootScope, $compile) {
      $scope = $rootScope.$new();
      buildDirective = function (withRefineSearch) {
        var element = angular.element('<div ' + (withRefineSearch ? 'refine-search' : '') + '><div><contact-actions></contact-actions></div></div>');

        return $compile(element)($scope);
      };
    }));

    describe('when the "refine-search" attribute is present in the grand parent element', function () {
      beforeEach(function () {
        buildDirective(true);
        $scope.$digest();
      });

      it('sets the "refineSearchVisible" on the controller to true', function () {
        expect($scope.$ctrl.refineSearchVisible).toBeTruthy();
      });
    });

    describe('when the "refine-search" attribute is not present in the grand parent element', function () {
      beforeEach(function () {
        buildDirective(false);
        $scope.$digest();
      });

      it('sets the "refineSearchVisible" on the controller to false', function () {
        expect($scope.$ctrl.refineSearchVisible).toBeFalsy();
      });
    });
  });
});
