/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/lodash',
  'common/angularMocks',
  'common/directives/timepicker-select.directive'
], function (angular, _) {
  'use strict';

  describe('timepickerSelect directive', function () {
    var vm, $rootScope, $compile;

    beforeEach(module('common.directives'));
    beforeEach(inject(function (_$rootScope_, _$compile_) {
      $rootScope = _$rootScope_;
      $compile = _$compile_;
    }));

    describe('when "timepicker-select" attribute is specified', function () {
      describe('when no additional attributes are passed', function () {
        beforeEach(function () {
          buildDirective();
        });

        it('builds correct amount of options', function () {
          expect(vm.options.length).toBe(24 * 60); // 00:00 - 23:59
        });
      });

      describe('when "timepicker-select-time-from" attribute is passed', function () {
        beforeEach(function () {
          buildDirective({ 'timepicker-select-time-from': '23:50' });
        });

        it('contains expected options', function () {
          expect(_.contains(vm.options, '23:50')).toBeTruthy();
          expect(_.contains(vm.options, '23:55')).toBeTruthy();
          expect(_.contains(vm.options, '23:59')).toBeTruthy();
        });

        it('does not contain unexpected options', function () {
          expect(_.contains(vm.options, '23:49')).toBeFalsy();
        });

        it('contains expected amount of options', function () {
          // 23:50 - 23:59
          expect(vm.options.length).toBe(10);
        });
      });

      describe('when "timepicker-select-time-to" attribute is passed', function () {
        beforeEach(function () {
          buildDirective({ 'timepicker-select-time-to': '00:09' });
        });

        it('contains expected options', function () {
          expect(_.contains(vm.options, '00:00')).toBeTruthy();
          expect(_.contains(vm.options, '00:05')).toBeTruthy();
          expect(_.contains(vm.options, '00:09')).toBeTruthy();
        });

        it('does not contain unexpected options', function () {
          expect(_.contains(vm.options, '00:10')).toBeFalsy();
        });

        it('contains expected amount of options', function () {
          // 00:00 - 00:10
          expect(vm.options.length).toBe(10);
        });
      });

      describe('when "timepicker-select-interval" attribute is passed', function () {
        beforeEach(function () {
          buildDirective({ 'timepicker-select-interval': '15' });
        });

        it('contains expected options', function () {
          expect(_.contains(vm.options, '00:00')).toBeTruthy();
          expect(_.contains(vm.options, '15:15')).toBeTruthy();
          expect(_.contains(vm.options, '20:30')).toBeTruthy();
          expect(_.contains(vm.options, '23:45')).toBeTruthy();
        });

        it('does not contain unexpected options', function () {
          expect(_.contains(vm.options, '00:01')).toBeFalsy();
          expect(_.contains(vm.options, '23:50')).toBeFalsy();
        });

        it('contains expected amount of options', function () {
          // 00:00 - 23:45 with a 15 minutes interval
          expect(vm.options.length).toBe(96);
        });
      });

      describe('when "timepicker-select-default-option" attribute is passed', function () {
        beforeEach(function () {
          buildDirective({ 'timepicker-select-default-option': 'Default option' });
        });

        it('sets the default option', function () {
          expect(vm.defaultOption).toBe('Default option');
        });
      });
    });

    function buildDirective (options) {
      var element, scopeKey;
      var attributes = '';
      var $scope = $rootScope.$new();

      options = options || {};

      _.each(options, function (optionValue, optionKey) {
        scopeKey = optionKey.replace(/-/g, '');
        $scope[scopeKey] = optionValue;
        attributes += ' ' + optionKey + '="' + scopeKey + '"';
      });

      element = angular.element(
        '<select timepicker-select' + attributes + '></select>');
      vm = $compile(element)($scope).controller('timepickerSelect');

      $scope.$digest();
    }
  });
});
