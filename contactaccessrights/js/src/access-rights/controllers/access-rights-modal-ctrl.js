define(['access-rights/modules/controllers'], function (controllers) {
	'use strict';

	controllers.controller('AccessRightsModalCtrl', ['Region', 'Location', '$log', '$rootScope', '$modalInstance',
		function (Region, Location, $log, $rootScope, $modalInstance) {
			$log.debug('AccessRightsModalCtrl');

			Region.getAll().then(function (data) {
				vm.availableData.regions = data;
			});

			Location.getAll().then(function (data) {
				vm.availableData.locations = data;
			});

			var vm = this;
			vm.cancel = function () {
				$modalInstance.dismiss('cancel');
			};

			vm.availableData = {
				regions: [],
				locations: []
			};

			vm.selectedData = {
				locations: [],
				regions: []
			};
		}
	]);
});
