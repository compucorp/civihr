/* eslint-env amd */

define(function () {
  'use strict';

  hrjcContact.$inject = ['$compile', '$log', 'settings', 'contactService'];

  function hrjcContact ($compile, $log, settings, contactService) {
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

        $scope.$watch('contactId', function (contactId) {
          contactService.getOne($scope.contactId).then(function (contact) {
            $scope.contact = contact;

            if ($scope.renderAsLink) {
              el.html('<a ng-href="/civicrm/contact/view?reset=1&cid={{contactId}}">{{contact.label}}</a>');
              $compile(el.contents())($scope);
            }
          });
        });
      }
    };
  }

  return { hrjcContact: hrjcContact };
});
