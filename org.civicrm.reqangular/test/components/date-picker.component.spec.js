/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/angularMocks',
  'common/components/date-picker.component'
], function () {
  'use strict';

  describe('Date Picker component', function () {
    var $componentController, ctrl;

    beforeEach(module('common.components'));

    beforeEach(inject(function (_$componentController_) {
      $componentController = _$componentController_;
    }));

    describe('convertToHTML5DatepickerFormat()', function () {
      var date, returnValue;

      beforeEach(function () {
        setupController();
        date = new Date('2017-09-18');
        returnValue = ctrl.convertToHTML5DatepickerFormat(date);
      });

      it('formats the date into string format', function () {
        expect(returnValue).toBe('2017-09-18');
      });
    });

    describe('clearBtnClick()', function () {
      var clearBtnClick;

      describe('when clearBtnClick is passed', function () {
        beforeEach(function () {
          clearBtnClick = jasmine.createSpy('');
          setupController({ clearBtnClick: clearBtnClick });

          ctrl.clearButtonClick();
        });

        it('calls the passed function', function () {
          expect(clearBtnClick).toHaveBeenCalled();
        });
      });

      describe('when clearBtnClick is not passed', function () {
        beforeEach(function () {
          setupController();
          ctrl.ngModelCtrl = jasmine.createSpyObj('ngModelCtrl', ['$setViewValue']);
          ctrl.clearButtonClick();
        });

        it('sets the ngModel to false', function () {
          expect(ctrl.ngModel).toBe(false);
        });
      });
    });

    /**
     * Sets up the datePicker controller for testing purposes.
     */
    function setupController (bindings) {
      ctrl = $componentController('datePicker', null, bindings);
    }
  });
});
