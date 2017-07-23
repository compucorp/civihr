/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'common/lodash',
    'mocks/data/absence-type-data',
    'leave-absences/shared/components/leave-calendar-day.component'
  ], function (_, AbsenceTypeData) {
    'use strict';

    describe('leaveCalendarDay', function () {
      var $componentController, $log;

      beforeEach(module('leave-absences.templates', 'leave-absences.components'));
      beforeEach(inject(function (_$componentController_, _$log_, _$rootScope_) {
        $componentController = _$componentController_;
        $log = _$log_;

        spyOn($log, 'debug');
        compileComponent();
      }));

      it('is initialized', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      function compileComponent () {
        $componentController('leaveCalendarDay');
      }
    });
  });
}(CRM));
