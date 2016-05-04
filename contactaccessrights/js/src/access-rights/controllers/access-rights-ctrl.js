define(['access-rights/modules/controllers'], function (controllers) {
	'use strict';
	controllers.controller('AccessRightsCtrl', ['$log', '$rootElement', '$modal',
		function ($log, $rootElement, $modal) {
      var vm = this;
			$log.debug('AccessRightsCtrl');

			return {
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
