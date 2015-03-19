define(['directives/directives'], function(directives){
    directives.directive('hrjcContact',['$compile', 'ContactService', 'settings',
        '$log', function($compile, ContactService, settings, $log){
        $log.debug('Directive: hrjcContact');

        return {
            restrict: 'A',
            scope: {
                renderAsLink: '=?hrjcContactLink',
                contactId: '=?hrjcContact'
            },
            template: '{{contact.label}}',
            link: function ($scope, el) {
                if (!$scope.contactId) {
                    return;
                }

                $scope.$watch('contactId',function(contactId){
                    ContactService.getOne($scope.contactId).then(function(contact){
                        $scope.contact = contact;

                        if ($scope.renderAsLink) {
                            el.html('<a ng-href="/civicrm/contact/view?reset=1&cid={{contactId}}">{{contact.label}}</a>');
                            $compile(el.contents())($scope);
                        }
                    });
                });
            }
        }
    }]);
});