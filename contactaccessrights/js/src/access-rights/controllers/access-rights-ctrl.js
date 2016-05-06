define(['access-rights/modules/controllers'], function (controllers) {
	'use strict';
	controllers.controller('AccessRightsCtrl', ['$rootElement', '$modal',
		function ($rootElement, $modal) {
			return {

        /**				
         * Opens the permissions modal
         */
        openModal: function () {
          $modal.open({
            targetDomEl: $rootElement.children().eq(0),
            controller: 'AccessRightsModalCtrl',
            controllerAs: 'modalCtrl',
            bindToController: true,
            templateUrl: CRM.vars.contactAccessRights.baseURL + '/views/access-rights-modal.html'
          });
        }
      };
		}
	]);
});
