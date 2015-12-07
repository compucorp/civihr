define([
    'common/angularMocks',
    'appraisals/app',
], function () {
    'use strict';

    describe('AppraisalsCtrl', function () {
        var $log, $scope, ctrl;

        beforeEach(module('appraisals'));

        beforeEach(inject(function ($rootScope, _$log_, $controller) {
            ($log = _$log_) && spyOn($log, 'debug');

            $scope = $rootScope.$new();
            ctrl = $controller('AppraisalsCtrl', { $scope: $scope });
        }));

        it('is initialized', function () {
            expect($log.debug).toHaveBeenCalled();
        });
    });
})
