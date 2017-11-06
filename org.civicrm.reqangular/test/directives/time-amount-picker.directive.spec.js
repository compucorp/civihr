/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/lodash',
  'common/angularMocks',
  'common/directives/time-amount-picker.directive'
], function (angular, _) {
  'use strict';

  describe('timeAmountPicker directive', function () {
    var vm, $compile, $scope, $rootScope;

    beforeEach(module('common.directives'));
    beforeEach(inject(function (_$rootScope_, _$compile_) {
      $rootScope = _$rootScope_;
      $compile = _$compile_;
    }));

    describe('when "time-amount-picker" attribute is specified', function () {
      describe('when no additional attributes are passed', function () {
        beforeEach(function () {
          buildDirective();
        });

        it('builds correct amount of hours options', function () {
          expect(vm.hoursOptions.length).toBe(25);
        });

        it('builds correct amount of minutes options', function () {
          expect(vm.minutesOptions.length).toBe(60);
        });
      });

      describe('when "time-amount-picker-min-amount" attribute is passed', function () {
        beforeEach(function () {
          buildDirective([
            { key: 'time-amount-picker-min-amount', value: '2.5', bind: '>' }
          ]);
        });

        it('builds correct amount of hours options', function () {
          expect(vm.hoursOptions.length).toBe(23);
        });

        it('has expected hours options', function () {
          expect(_.includes(vm.hoursOptions, 3)).toBeTruthy();
          expect(_.includes(vm.hoursOptions, 5)).toBeTruthy();
          expect(_.includes(vm.hoursOptions, 24)).toBeTruthy();
        });

        it('builds correct amount of minutes options', function () {
          expect(vm.minutesOptions.length).toBe(60);
        });

        describe('when hours value is changed and it is less than the minimum specified', function () {
          beforeEach(function () {
            vm.selectedHours = 2;
            vm.buildMinutesOptions();
          });

          it('builds correct amount of minutes options', function () {
            expect(vm.minutesOptions.length).toBe(30);
          });
        });
      });

      describe('when "time-amount-picker-max-amount" attribute is passed', function () {
        beforeEach(function () {
          buildDirective([
            { key: 'time-amount-picker-max-amount', value: '7.5', bind: '>' }
          ]);
        });

        it('builds correct amount of hours options', function () {
          expect(vm.hoursOptions.length).toBe(8);
        });

        it('has expected hours options', function () {
          expect(_.includes(vm.hoursOptions, 0)).toBeTruthy();
          expect(_.includes(vm.hoursOptions, 3)).toBeTruthy();
          expect(_.includes(vm.hoursOptions, 7)).toBeTruthy();
        });

        it('builds correct amount of minutes options', function () {
          expect(vm.minutesOptions.length).toBe(60);
        });

        describe('when hours value is changed and it is same as the maximum specified', function () {
          beforeEach(function () {
            vm.selectedHours = 7;
            vm.buildMinutesOptions();
          });

          it('builds correct amount of minutes options', function () {
            expect(vm.minutesOptions.length).toBe(31);
          });
        });
      });

      describe('when "time-amount-picker-max-amount" is 0', function () {
        beforeEach(function () {
          buildDirective([
            { key: 'time-amount-picker-max-amount', value: '0', bind: '>' }
          ]);
        });

        it('builds correct amount of options', function () {
          expect(vm.hoursOptions).toEqual([0]);
          expect(vm.minutesOptions).toEqual([0]);
        });
      });

      describe('when "time-amount-picker-value" attribute is passed', function () {
        var valueObject = { value: '6.5' };

        beforeEach(function () {
          buildDirective([
            { key: 'time-amount-picker-value', value: valueObject.value, bind: '=' }
          ]);
        });

        it('sets hours', function () {
          expect(vm.selectedHours).toBe('6');
        });

        it('sets minutes', function () {
          expect(vm.selectedMinutes).toBe('30');
        });

        describe('when selected values are changed', function () {
          beforeEach(function () {
            vm.selectedHours = 8;
            vm.selectedMinutes = 45;

            vm.calculateSelectedValue();
          });

          it('sets the result value back', function () {
            expect($scope.$$childTail.value).toBe(8.75);
          });
        });
      });

      describe('when "time-amount-picker-disabled" attribute is passed', function () {
        beforeEach(function () {
          buildDirective([
            { key: 'time-amount-picker-disabled', value: 'true', bind: '<' }
          ]);
        });

        it('sets the "disabled" property', function () {
          expect(vm.disabled).toBe('true');
        });
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
      var element = angular.element('<time-amount-picker></time-amount-picker>');

      $scope = $rootScope.$new();
      options = options || [];

      _.each(options, function (option) {
        // Depending on a binding type a wrapper might be needed
        attrWrapper = option.bind === '<' ? '"' : '';

        element.attr(option.key, attrWrapper + option.value + attrWrapper);
      });

      vm = $compile(element)($scope).controller('timeAmountPicker');

      $scope.$digest();
    }
  });
});
