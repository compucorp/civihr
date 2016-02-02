define([
    'common/angular'
], function (angular) {
    'use strict';

    return angular.module('compuRoute', ['ngRoute'])
        .config(['$routeProvider', function ($routeProvider) {
            var originalWhen = $routeProvider.when;
            var commonResolve = {};

            // Stores the values that need to be resolved for all paths
            $routeProvider.resolveForAll = function (_commonResolve) {
                commonResolve = _commonResolve;

                return this;
            };

            // Extension of default `when`
            $routeProvider.when = function (path, route) {
                // Injects the common resolves in the route's `resolve` object
                angular.extend(route.resolve || {}, commonResolve);

                // calls the original method
                return originalWhen.call($routeProvider, path, route);
            };
        }]);
});
