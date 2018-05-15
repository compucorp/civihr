/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'leave-absences/mocks/data/absence-type.data',
  'leave-absences/shared/components/leave-calendar-legend.component'
], function (_, AbsenceTypeData) {
  'use strict';

  describe('leaveCalendarLegend', function () {
    var $componentController, $log, controller, mockedAbsenceTypes;

    beforeEach(module('leave-absences.templates', 'leave-absences.components'));
    beforeEach(inject(function (_$componentController_, _$log_, _$rootScope_) {
      $componentController = _$componentController_;
      $log = _$log_;

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

    it('has a list of "other" badges', function () {
      expect(controller.other_badges).toEqual(jasmine.any(Array));
      expect(controller.other_badges[0]).toEqual(jasmine.objectContaining({
        label: jasmine.any(String),
        hint: jasmine.any(String)
      }));
      expect(controller.other_badges.length).toBe(5);
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

    function compileComponent () {
      controller = $componentController('leaveCalendarLegend', null, { absenceTypes: mockedAbsenceTypes });
    }
  });
});
