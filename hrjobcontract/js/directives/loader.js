define(['directives/directives'], function(directives){
    directives.directive('hrjcLoader',['$rootScope','$log',function($rootScope, $log){
        $log.debug('Directive: hrjcLoader');

        return {
            link: function ($scope, el, attrs) {
                var loader = document.createElement('div'),
                    positionSet = false;

                loader.className = 'hrjc-loader';

                function isPositioned(){
                    var elPosition = window.getComputedStyle(el[0]).position;
                    return elPosition == 'relative' || elPosition == 'absolute' || elPosition == 'fixed'
                }

                function appendLoader() {
                    if (!isPositioned()) {
                        el.css('position','relative');
                        positionSet = true;
                    }
                    el.append(loader);
                }

                function removeLoader(){
                    loader.remove();
                    if (positionSet) {
                        el.css('position','');
                    }
                }

                if (attrs.hrjcLoaderShow) {
                    appendLoader();
                }

                $scope.$on('hrjc-loader-show',function(){
                    appendLoader();
                });

                $scope.$on('hrjc-loader-hide',function(){
                    removeLoader();
                });

            }
        }
    }]);
});