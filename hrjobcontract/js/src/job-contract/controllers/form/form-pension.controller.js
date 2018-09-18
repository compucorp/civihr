/* eslint-env amd */

define(function () {
  'use strict';

  FormPensionController.$inject = ['$log', '$scope', 'settings', 'contactService'];

  function FormPensionController ($log, $scope, settings, contactService) {
    $log.debug('Controller: FormPensionController');

    $scope.contacts = {
      Pension_Provider: []
    };

    $scope.refreshContacts = refreshContacts;

    (function init () {
      if ($scope.entity.pension.pension_type) {
        contactService.getOne($scope.entity.pension.pension_type).then(function (provider) {
          $scope.contacts.Pension_Provider.push(provider);
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
      }).then(function (contactSubTypes) {
        $scope.contacts[contactSubType] = contactSubTypes;
      });
    }
  }

  return { FormPensionController: FormPensionController };
});
