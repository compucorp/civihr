define([
  'leave-absences/my-leave/modules/directives'
], function (directives){
    'use strict';

    directives.directive('testDirective', ['$log', function($log){
      $log.debug('leaveRequestModal');
console.log(1);
      return{
        // scope: {
        //   leaveRequest: '='
        // },
        controller: function (){
          this.doSomething = function(){
            console.log('doSomething in controller');
          }
        },
        restrict: 'E',
        link: function(scope, element, attrs, ctrl){
          $log.debug('link');
          ctrl.doSomething();
          scope.test = 'test';
          console.log(2);
element.append('<span>This span is appended from directive.</span>');
        }
      };
    }]);
});
