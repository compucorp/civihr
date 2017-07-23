/* eslint-env amd, jasmine */

define([
  'leave-absences/shared/components/leave-calendar-month.component'
], function () {
  'use strict';

  describe('leaveCalendarMonth', function () {
    var $componentController, $log, controller;

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

    it('has the maximum size of the contacts list per page defined', function () {
      expect(controller.pageSize).toBeDefined();
      expect(controller.pageSize).toEqual(jasmine.any(Number));
    });

    it('has the current page set to zero', function () {
      expect(controller.currentPage).toBe(0);
    });

    it('has the show contact name setting set to false by default', function () {
      expect(controller.showContactName).toBe(false);
    });

    function compileComponent () {
      controller = $componentController('leaveCalendarMonth');
    }
  });
});
