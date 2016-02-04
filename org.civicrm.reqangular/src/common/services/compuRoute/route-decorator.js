define([
    'common/angular',
    'common/modules/compuRoute'
], function (angular, module) {
    'use strict';

    module.factory('RouteDecorator', function () {
        return {
            /**
             * Decorator function that provides new functionality either to $stateProvider or $routeProvider
             * enabling them to declare common resolve across all states/routes using .resolveForAll() method.
             * @param provider
             */
            enableCommonResolve: function (provider) {

                /**
                 * Extend original state/when function by merging commonResolve object into it's own resolve.
                 * @param {Function} original
                 * @returns {Function}
                 */
                function extendRoutingFunction(original) {
                    return function (state, options) {
                        // Injects the common resolves in the route's `resolve` object
                        angular.extend(options.resolve || {}, provider.commonResolve || {});

                        // calls the original method
                        return original.call(provider, state, options);
                    }
                }

                /**
                 * Share resolve across all states
                 * @param {object} _commonResolve
                 * @returns Provider
                 */
                provider.resolveForAll = function (_commonResolve) {
                    provider.commonResolve = _commonResolve;
                    return provider;
                };

                // check if it's a ngRoute
                if (provider.when) {
                    provider.when = extendRoutingFunction(provider.when);
                    // check if it's a ui-router
                } else if (provider.state) {
                    provider.state = extendRoutingFunction(provider.state);
                } else {
                    throw new Error('Invalid parameter!');
                }

                return provider;
            }
        }
    });

});
