define([
  'job-contract/controllers/controllers',
  'job-contract/services/contact'
], function (controllers) {
  'use strict';

  controllers.controller('FormPensionCtrl',['$scope','settings','ContactService', '$log',
    function ($scope, settings, ContactService, $log) {
      $log.debug('Controller: FormPensionCtrl');

      $scope.contacts = {
        Pension_Provider: []
      };

      (function init() {
        if ($scope.entity.pension.pension_type) {
          ContactService.getOne($scope.entity.pension.pension_type).then(function (provider) {
            $scope.contacts.Pension_Provider.push(provider);
          });
        }
      }());

      $scope.refreshContacts = function(input, contactSubType){
        if (!input) {
          return;
        }

        ContactService.search(input, {
          contact_type: 'Organization',
          contact_sub_type: contactSubType
        }).then(function (contactSubTypes) {
          $scope.contacts[contactSubType] = contactSubTypes;
        });
      };
    }]);
});
