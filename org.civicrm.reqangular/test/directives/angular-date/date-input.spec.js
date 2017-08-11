/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/angularMocks',
  'common/directives/angular-date/date-input'
], function (angular) {
  'use strict';

  describe('dateInput Directive', function () {
    var Element, scope, compile;

    beforeEach(function () {
      module('common.angularDate');

      inject(function ($compile, $rootScope) {
        compile = $compile;
        scope = $rootScope.$new();
      });

      Element = (function () {
        scope.CalendarShow = {
          'start_date': false
        };

        scope.minDate = new Date(2000, 1, 1);
        scope.maxDate = new Date(2020, 11, 1);
        scope.edit_data = {
          10: {
            'start_date': new Date()
          }
        };

        scope.select = function (name) {
          return 'selected ' + name;
        };

        var element = angular.element('<input type="text" class="form-control"\n' +
                    'id="start_date"\n' +
                    'name="start_date"\n' +
                    'datepicker-popup\n' +
                    'is-open="CalendarShow.start_date"\n' +
                    'min-date="minDate"\n' +
                    'ng-model="edit_data[10].start_date"\n' +
                    "ng-change=\"select('start_date')\"\n" +
                    'ng-disabled="isDisabled"\n' +
                    'close-text="Close"\n' +
                    'date-input\n' +
                    'required />');

        var compiledElement = compile(element)(scope);
        scope.$digest();
        return compiledElement;
      })();
    });

    it('Should be defined', function () {
      expect(Element).toBeDefined();
    });

    it('Should refresh input', function () {
      scope.edit_data[10].start_date = new Date(2014, 2, 2);
      scope.$digest();
      expect(Element[0].value).toEqual('2014-03-02');
    });
  });
});
