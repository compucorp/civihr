/* eslint-env amd */

define([
  'common/angular'
], function (angular) {
  'use strict';

  return {
    /**
     * Adds the necessary elements to the DOM so the app can be initialized.
     * This approach was used to make the extension self contained, avoiding the
     * overriding of the ".tpl" file for the page and possibly generating
     * conflicts with other extensions.
     *
     * @return {angular element} The angular element for the app
     */
    addAppToDOM: function () {
      var actionsElement = angular.element('.crm-actions-ribbon ul#actions');
      var appElement = angular.element('<li id="access-rights"> ' +
          '<div ng-controller="AccessRightsCtrl as $ctrl" id="bootstrap-theme"> ' +
            '<a href class="edit button pull-right" ng-click="$ctrl.openModal()" id="manage-roles-and-teams"> ' +
              '<div class="crm-i fa-edit"></div> Manage roles and teams ' +
            '</a> ' +
          '</div> ' +
        '</li>');
      actionsElement.append(appElement);
      return appElement;
    }
  };
});
