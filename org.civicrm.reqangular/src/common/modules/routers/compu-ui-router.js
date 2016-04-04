define([
    'common/angular',
    'common/angularUiRouter'
], function (angular) {
    'use strict';

    return angular.module("compu.ui.router", ['ui.router']).config(['$stateProvider', function($stateProvider){
        var originalState = $stateProvider.state;
        /**
         * Share resolve across all states
         * @param {object} _commonResolve
         * @returns Provider
         */
        $stateProvider.resolveForAll = function (_commonResolve) {
            $stateProvider.commonResolve = _commonResolve;
            return $stateProvider;
        };

        $stateProvider.state = function (state, options) {
            // Injects the common resolves in the states' `resolve` object
            angular.extend(options.resolve || {}, $stateProvider.commonResolve || {});

            // calls the original method
            return originalState.call($stateProvider, state, options);
        };
    }]);
});
