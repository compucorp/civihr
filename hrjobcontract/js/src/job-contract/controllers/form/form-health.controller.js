/* eslint-env amd, jasmine */

define([
  'job-contract/controllers/controllers',
  'job-contract/services/contact'
], function (controllers) {
  'use strict';

  controllers.controller('FormHealthCtrl', ['$scope', 'ContactService', '$log',
    function ($scope, ContactService, $log) {
      $log.debug('Controller: FormHealthCtrl');

      $scope.contacts = {
        Health_Insurance_Provider: [],
        Life_Insurance_Provider: []
      };

      $scope.refreshContacts = function (input, contactSubType) {
        if (!input) {
          return;
        }

        ContactService.search(input, {
          contact_type: 'Organization',
          contact_sub_type: contactSubType
        }).then(function (results) {
          $scope.contacts[contactSubType] = results;
        });
      };

      if ($scope.entity.health.provider) {
        ContactService.getOne($scope.entity.health.provider).then(function (result) {
          $scope.contacts.Health_Insurance_Provider.push(result);
        });
      }

      if ($scope.entity.health.provider_life_insurance) {
        ContactService.getOne($scope.entity.health.provider_life_insurance).then(function (result) {
          $scope.contacts.Life_Insurance_Provider.push(result);
        });
      }
    }
  ]);
});
