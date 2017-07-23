/* eslint-env amd, jasmine */

define([
  'leave-absences/shared/components/leave-calendar-day.component'
], function () {
  'use strict';

  describe('leaveCalendarMonth', function () {
    var $componentController, $log;

    beforeEach(module('leave-absences.templates', 'leave-absences.components'));
    beforeEach(inject(function (_$componentController_, _$log_) {
      $componentController = _$componentController_;
      $log = _$log_;

      spyOn($log, 'debug');
      compileComponent();
    }));

    it('is initialized', function () {
      expect($log.debug).toHaveBeenCalled();
    });

    function compileComponent () {
      $componentController('leaveCalendarMonth');
    }
  });
});
