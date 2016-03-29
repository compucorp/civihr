define([
    'common/angularMocks',
    'appraisals/app',
    'common/mocks/services/api/appraisal-cycle-mock',
], function () {
    'use strict';

    describe('AppraisalCycleSummaryCtrl', function () {
        var $controller, $log, $modal, $scope, ctrl, cycle;

        beforeEach(module('appraisals'));
        beforeEach(inject([
            '$log', '$modal', '$controller', '$rootScope', 'api.appraisal-cycle.mock',
            function (_$log_, _$modal_, _$controller_, $rootScope, appraisalCycleAPIMock) {
                ($modal = _$modal_) && spyOn($modal, 'open');
                ($log = _$log_) && spyOn($log, 'debug');

                $controller = _$controller_;
                $scope = $rootScope.$new();

                cycle = appraisalCycleAPIMock.mockedCycles().list[0];

                initController();
            }
        ]));

        describe('init', function () {
            it('is initialized', function () {
                expect($log.debug).toHaveBeenCalled();
            });

            it('contains grades', function () {
                expect(ctrl.grades).toBeDefined();
                expect(ctrl.grades).toEqual(jasmine.any(Array));
            });
        });

        describe('Edit Dates modal', function () {
            beforeEach(function () {
                ctrl.openEditDatesModal();
            });

            it('opens the modal', function () {
                expect($modal.open).toHaveBeenCalled();
            });
        });

        /**
         * Initializes the controllers with its dependencies injected
         */
        function initController() {
            $scope.cycle = { cycle: cycle };

            ctrl = $controller('AppraisalCycleSummaryCtrl', {
                $scope: $scope
            });
        }
    });
});
