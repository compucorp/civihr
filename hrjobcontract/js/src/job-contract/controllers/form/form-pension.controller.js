/* eslint-env amd */

define(function () {
  'use strict';

  FormPensionController.__name = 'FormPensionController';
  FormPensionController.$inject = ['$log', '$scope', 'settings', 'ContactService'];

  function FormPensionController ($log, $scope, settings, ContactService) {
    $log.debug('Controller: FormPensionController');

    $scope.contacts = {
      Pension_Provider: []
    };

    $scope.refreshContacts = refreshContacts;

    (function init () {
      if ($scope.entity.pension.pension_type) {
        ContactService.getOne($scope.entity.pension.pension_type).then(function (provider) {
          $scope.contacts.Pension_Provider.push(provider);
        });
      }
    }());

    function refreshContacts (input, contactSubType) {
      if (!input) {
        return;
      }

      ContactService.search(input, {
        contact_type: 'Organization',
        contact_sub_type: contactSubType
      }).then(function (contactSubTypes) {
        $scope.contacts[contactSubType] = contactSubTypes;
      });
    }
  }

  return FormPensionController;
});
