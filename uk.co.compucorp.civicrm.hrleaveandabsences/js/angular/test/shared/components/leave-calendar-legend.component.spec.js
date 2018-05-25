/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'leave-absences/mocks/data/absence-type.data',
  'leave-absences/shared/components/leave-calendar-legend.component'
], function (_, AbsenceTypeData) {
  'use strict';

  describe('leaveCalendarLegend', function () {
    var $componentController, $log, $rootScope, controller, mockedAbsenceTypes;

    beforeEach(module('leave-absences.templates', 'leave-absences.components'));
    beforeEach(inject(function (_$componentController_, _$log_, _$rootScope_) {
      $componentController = _$componentController_;
      $log = _$log_;
      $rootScope = _$rootScope_;

      mockedAbsenceTypes = AbsenceTypeData.all().values;

      spyOn($log, 'debug');
      compileComponent();
    }));

    it('is initialized', function () {
      expect($log.debug).toHaveBeenCalled();
    });

    it('is collapsed', function () {
      expect(controller.legendCollapsed).toBe(true);
    });

    it('has a list of "non working" day types badges', function () {
      expect(controller.nonWorkingDayTypes).toEqual(jasmine.objectContaining([{
        label: jasmine.any(String),
        cssClassSuffix: jasmine.any(String)
      }]));
    });

    it('has a total of 3 "non working" day types badges', function () {
      expect(controller.nonWorkingDayTypes.length).toBe(3);
    });

    it('has a list of "other" badges', function () {
      expect(controller.otherBadges).toEqual(jasmine.objectContaining([{
        label: jasmine.any(String),
        description: jasmine.any(String)
      }]));
    });

    it('has a total of 5 "other" badges', function () {
      expect(controller.otherBadges.length).toBe(5);
    });

    describe('getAbsenceTypeStyle()', function () {
      var style, absenceType;

      beforeEach(function () {
        absenceType = _.sample(mockedAbsenceTypes);
        style = controller.getAbsenceTypeStyle(absenceType);
      });

      it('uses the color of the given absence type to define border and background colors', function () {
        expect(style).toEqual({
          backgroundColor: absenceType.color,
          borderColor: absenceType.color
        });
      });
    });

    describe('absence types filter selection storage', function () {
      beforeEach(function () {
        spyOn($rootScope, '$emit');
        $rootScope.$digest();
      });

      it('has all absence types selelected', function () {
        expect(controller.checkIfAbsenceTypeIsSelectedForFiltering('1')).toEqual(true);
        expect(controller.checkIfAbsenceTypeIsSelectedForFiltering('2')).toEqual(true);
        expect(controller.checkIfAbsenceTypeIsSelectedForFiltering('3')).toEqual(true);
      });

      describe('when an absence type is selected', function () {
        beforeEach(function () {
          controller.toggleFilteringByAbsenceType('1');
          $rootScope.$digest();
        });

        it('has the absence type selected', function () {
          expect(controller.checkIfAbsenceTypeIsSelectedForFiltering('1')).toEqual(true);
        });

        it('has other absence type not selected', function () {
          expect(controller.checkIfAbsenceTypeIsSelectedForFiltering('2')).toEqual(false);
        });

        it('notifies parent controller', function () {
          expect($rootScope.$emit).toHaveBeenCalledWith(
            'LeaveCalendar::updateFiltersByAbsenceType', ['1']);
        });

        describe('when the same absence type is deselected', function () {
          beforeEach(function () {
            controller.toggleFilteringByAbsenceType('1');
          });

          it('selects all absence types back', function () {
            expect(controller.checkIfAbsenceTypeIsSelectedForFiltering('1')).toEqual(true);
            expect(controller.checkIfAbsenceTypeIsSelectedForFiltering('2')).toEqual(true);
            expect(controller.checkIfAbsenceTypeIsSelectedForFiltering('3')).toEqual(true);
          });
        });

        describe('when another absence type is selected', function () {
          beforeEach(function () {
            controller.toggleFilteringByAbsenceType('2');
          });

          it('contains multiple absence types', function () {
            expect(controller.checkIfAbsenceTypeIsSelectedForFiltering('1')).toEqual(true);
            expect(controller.checkIfAbsenceTypeIsSelectedForFiltering('2')).toEqual(true);
          });

          it('leave other absence types not selected', function () {
            expect(controller.checkIfAbsenceTypeIsSelectedForFiltering('3')).toEqual(false);
          });

          describe('when absence types filter is reset', function () {
            beforeEach(function () {
              controller.resetFilteringByAbsenceTypes();
            });

            it('selects all absence types back', function () {
              expect(controller.checkIfAbsenceTypeIsSelectedForFiltering('1')).toEqual(true);
              expect(controller.checkIfAbsenceTypeIsSelectedForFiltering('2')).toEqual(true);
              expect(controller.checkIfAbsenceTypeIsSelectedForFiltering('3')).toEqual(true);
            });
          });
        });
      });
    });

    function compileComponent () {
      controller = $componentController('leaveCalendarLegend', null, { absenceTypes: mockedAbsenceTypes });
    }
  });
});
