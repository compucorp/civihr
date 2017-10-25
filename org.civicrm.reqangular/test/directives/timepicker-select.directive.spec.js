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

        it('does not set a placeholder', function () {
          expect(!!vm.placeholder).toBeFalsy();
        });
      });

      describe('when "timepicker-select-time-from" attribute is passed', function () {
        beforeEach(function () {
          buildDirective([{ key: 'timepicker-select-time-from', value: '23:50', bind: '<' }]);
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
          buildDirective([{ key: 'timepicker-select-time-to', value: '00:09', bind: '<' }]);
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
          buildDirective([{ key: 'timepicker-select-interval', value: '15', bind: '<' }]);
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

        describe('when "timepicker-select-interval" attribute is not a divider of 60', function () {
          beforeEach(function () {
            buildDirective([{ key: 'timepicker-select-interval', value: '17', bind: '<' }]);
          });

          it('contains expected options', function () {
            expect(_.contains(vm.options, '00:00')).toBeTruthy();
            expect(_.contains(vm.options, '00:17')).toBeTruthy();
            expect(_.contains(vm.options, '23:31')).toBeTruthy();
            expect(_.contains(vm.options, '23:48')).toBeTruthy();
          });

          it('contains expected amount of options', function () {
            // 00:00, 00:17 ..., 23:31, 23:48
            expect(vm.options.length).toBe(85);
          });
        });
      });

      describe('when "timepicker-select-placeholder" attribute is passed', function () {
        var placeholder = 'Please select time';

        beforeEach(function () {
          buildDirective([{ key: 'timepicker-select-placeholder', value: placeholder, bind: '@' }]);
        });

        it('sets the placeholder', function () {
          // need to add '"' because the binder is "@"
          expect(vm.placeholder).toBe(placeholder);
        });
      });
    });

    describe('when "timepicker-select-time-from" attribute is greater than "timepicker-select-time-to"', function () {
      beforeEach(function () {
        buildDirective([
          { key: 'timepicker-select-time-from', value: '14:00', bind: '<' },
          { key: 'timepicker-select-time-to', value: '9:00', bind: '<' }
        ]);
      });

      it('simply does not have any options', function () {
        expect(vm.options.length).toBe(0);
      });
    });

    /**
     * Builds a directive
     *
     * @param {Array} options objects of attributes keys, values and binding types
     *   [{ key: 'key', value: 'value', bind: '<|@' }, {...}, ...]
     */
    function buildDirective (options) {
      var attrWrapper = '';
      var element = angular.element('<select timepicker-select></select>');
      var $scope = $rootScope.$new();

      options = options || [];

      _.each(options, function (option) {
        // Depending on a binding type a wrapper might be needed
        attrWrapper = option.bind === '<' ? '"' : '';

        element.attr(option.key, attrWrapper + option.value + attrWrapper);
      });

      vm = $compile(element)($scope).controller('timepickerSelect');

      $scope.$digest();
    }
  });
});
