define([
    'common/angular',
    'common/angularRoute'
], function (angular) {
    'use strict';

    return angular.module("compuNgRoute", ['ngRoute']).config(['$routeProvider', function($routeProvider){
        var originalWhen = $routeProvider.when;

        /**
         * Share resolve across all states
         * @param {object} _commonResolve
         * @returns Provider
         */
        $routeProvider.resolveForAll = function (_commonResolve) {
            $routeProvider.commonResolve = _commonResolve;
            return $routeProvider;
        };

        $routeProvider.when = function (state, options) {
            // Injects the common resolves in the route's `resolve` object
            angular.extend(options.resolve || {}, $routeProvider.commonResolve || {});

            // calls the original method
            return originalWhen.call($routeProvider, state, options);
        };
    }]);
});
