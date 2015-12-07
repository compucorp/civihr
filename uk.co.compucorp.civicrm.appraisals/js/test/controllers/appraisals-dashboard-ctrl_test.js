define([
    'common/angularMocks',
    'appraisals/app',
], function () {
    'use strict';

    describe('AppraisalsDashboardCtrl', function () {
        var $log, ctrl;

        beforeEach(module('appraisals'));

        beforeEach(inject(function ($rootScope, _$log_, $controller) {
            ($log = _$log_) && spyOn($log, 'debug');

            ctrl = $controller('AppraisalsDashboardCtrl', { $scope: $rootScope.$new() });
        }));

        describe('init', function () {
            it('is initialized', function () {
                expect($log.debug).toHaveBeenCalled();
            });

            it('has the filters form collapsed', function () {
                expect(ctrl.filtersCollapsed).toBe(true);
            });
        });
    });
})
