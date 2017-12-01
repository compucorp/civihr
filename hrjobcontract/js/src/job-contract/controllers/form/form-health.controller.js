/* eslint-env amd */

define(function () {
  'use strict';

  FormHealthController.__name = 'FormHealthController';
  FormHealthController.$inject = ['$log', '$scope', 'contactService'];

  function FormHealthController ($log, $scope, contactService) {
    $log.debug('Controller: FormHealthController');

    $scope.contacts = {
      Health_Insurance_Provider: [],
      Life_Insurance_Provider: []
    };

    $scope.refreshContacts = refreshContacts;

    (function init () {
      if ($scope.entity.health.provider) {
        contactService.getOne($scope.entity.health.provider).then(function (result) {
          $scope.contacts.Health_Insurance_Provider.push(result);
        });
      }

      if ($scope.entity.health.provider_life_insurance) {
        contactService.getOne($scope.entity.health.provider_life_insurance).then(function (result) {
          $scope.contacts.Life_Insurance_Provider.push(result);
        });
      }
    }());

    function refreshContacts (input, contactSubType) {
      if (!input) {
        return;
      }

      contactService.search(input, {
        contact_type: 'Organization',
        contact_sub_type: contactSubType
      }).then(function (results) {
        $scope.contacts[contactSubType] = results;
      });
    }
  }

  return FormHealthController;
});
