define([
  'common/modules/directives',
  'common/controllers/contact-actions/contact-actions-ctrl',
  'common/directives/loading'
], function (directives) {
  'use strict';

  directives.directive('contactActions', [function () {
    return {
      restrict: 'E',
      templateUrl: 'contact-actions/contact-actions.html',
      controller: 'ContactActionsCtrl',
      controllerAs: '$ctrl',
      link: function (scope, element, attrs) {
        scope.$ctrl.refineSearchVisible = element.parent().parent()[0].hasAttribute('refine-search');
      }
    };
  }]);
});
