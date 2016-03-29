define([
    'common/angularMocks',
    'appraisals/app'
], function () {
    'use strict';

    describe('AppraisalCycleAppraisalsCtrl', function () {
        var $controller, $log, $modal, $provide, $scope, ctrl,
            dialog;

        beforeEach(module('appraisals'));
        beforeEach(inject(function (_$log_, _$modal_, _$controller_, _dialog_, $rootScope) {
            ($modal = _$modal_) && spyOn($modal, 'open');
            ($log = _$log_) && spyOn($log, 'debug');

            $controller = _$controller_;
            $scope = $rootScope.$new();

            dialog = _dialog_;

            initController();
        }));

        describe('init', function () {
            it('is initialized', function () {
                expect($log.debug).toHaveBeenCalled();
            });

            it('stores on scope the data passed by ui-router', function () {
                expect(ctrl.departments).toBeDefined();
                expect(ctrl.levels).toBeDefined();
                expect(ctrl.locations).toBeDefined();
                expect(ctrl.regions).toBeDefined();
            });

            it('has the filters form collapsed', function () {
                expect(ctrl.filtersCollapsed).toBe(true);
            });
        });

        /**
         * Initializes the controllers with its dependencies injected
         */
        function initController() {
            ctrl = $controller('AppraisalCycleAppraisalsCtrl', {
                $scope: $scope,
                departments: [],
                levels: [],
                locations: [],
                regions: []
            });
        }
    });
});
