define([
    'common/angularMocks',
    'appraisals/app'
], function () {
    'use strict';

    describe('AddAppraisalCycleModalCtrl', function () {
        var ctrl;

        beforeEach(module('appraisals'));
        beforeEach(inject(function ($controller, _$modal_) {
            var $modal = _$modal_;

            spyOn($modal, 'open').and.returnValue({});

            ctrl = $controller('AddAppraisalCycleModalCtrl', {
                $modalInstance: $modal.open()
            });
        }));

        describe('inheritance', function () {
            it('inherits from BasicModalCtrl', function () {
                expect(ctrl.cancel).toBeDefined();
            });
        });

        describe('date picker', function () {
            describe('on init', function () {
                it('has the calendar closed', function () {
                    expect(ctrl.calendarOpen).toBe(false);
                });
            });

            describe('on calendar open request', function () {
                beforeEach(function () {
                    ctrl.openCalendar();
                });

                it('opens the calendar', function () {
                    expect(ctrl.calendarOpen).toBe(true);
                });
            });
        });
    });
});
