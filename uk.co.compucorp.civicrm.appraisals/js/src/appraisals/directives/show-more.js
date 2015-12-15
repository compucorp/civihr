define([
    'appraisals/modules/directives'
], function (directives) {
    directives.directive('crmShowMore', ['$log', function ($log) {

        /**
         * Validates that the mandatory params are passed to the directive
         */
        function validateParams(attrs){
            if (typeof attrs.callback === 'undefined') {
                $log.error('[callback] is mandatory');
            }

            if (typeof attrs.done === 'undefined') {
                $log.error('[done] is mandatory');
            }
        }

        return {
            scope: {
                callback: '&',
                done: '='
            },
            transclude: true,
            replace: true,
            templateUrl: CRM.vars.appraisals.baseURL + '/views/directives/show-more.html',
            controllerAs: 'showMore',
            controller: ['$scope', function ($scope) {
                $log.debug('crmShowMore');

                this.callback = $scope.callback;
                // necessary because in this angular version we don't have `bindToController`
                $scope.$watch('done', function (newValue) {
                    this.done = newValue;
                }.bind(this));
            }],
            link: function (scope, element, attrs) {
                validateParams(attrs);
            }
        };
    }]);
});
