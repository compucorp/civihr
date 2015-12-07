define([
    'common/angularMocks',
    'appraisals/app',
], function () {
    'use strict';

    describe('AppraisalsDashboardCtrl', function () {
        var $log, $scope, ctrl;

        beforeEach(module('appraisals'));

        beforeEach(inject(function ($rootScope, _$log_, $controller) {
            ($log = _$log_) && spyOn($log, 'debug');

            $scope = $rootScope.$new();
            ctrl = $controller('AppraisalsDashboardCtrl', { $scope: $scope });
        }));

        it('is initialized', function () {
            expect($log.debug).toHaveBeenCalled();
        });
    });
})
